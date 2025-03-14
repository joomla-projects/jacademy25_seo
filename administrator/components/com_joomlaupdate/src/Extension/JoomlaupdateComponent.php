<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Config\ConfigServiceInterface;
use Joomla\CMS\Config\ConfigServiceTrait;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Version;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateRegisterState;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateState;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_config
 *
 * @since  __DEPLOY_VERSION__
 */
class JoomlaupdateComponent extends MVCComponent implements ConfigServiceInterface
{
    use ConfigServiceTrait;

    private const autoupdateUrl = 'https://autoupdate.joomla.org/api/v1';

    /**
     * Prepares the config form
     *
     * @param   Form          $form  The form to change
     * @param   array|object  $data  The form data
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    public function prepareForm(Form $form, $data): void
    {
        if ($form->getName() !== 'com_config.component') {
            return;
        }

        // If we want automated updates, check if we have an old key to use otherwise generate a new one
        $config = ComponentHelper::getParams('com_joomlaupdate');

        $token = $config->get('update_token');

        if (empty($token)) {
            $token = UserHelper::genRandomPassword(40);
        }

        $form->setFieldAttribute('update_token', 'default', $token);

        $data['update_token'] = $token;

        // Handle automated updates when form is submitted (but before it's saved)
        $input = Factory::getApplication()->getInput();

        if ($input->getMethod() === 'POST') {
            $dispatcher = Factory::getApplication()->getDispatcher();
            $dispatcher->addListener('onExtensionBeforeSave', [$this, 'onExtensionBeforeSave']);
            $dispatcher->addListener('onExtensionAfterSave', [$this, 'onExtensionAfterSave']);
        }
    }

    /**
     * Handles subscribe or unsubscribe from automated updates
     *
     * @param $event
     * @return void
     * @throws \Exception
     */
    public function onExtensionBeforeSave(\Joomla\CMS\Event\Model\BeforeSaveEvent $event)
    {
        $context = $event->getArgument('context');
        $table   = $event->getArgument('subject');

        if ($context !== 'com_config.component') {
            return;
        }

        $data = new Registry($table->params);

        $autoupdateStatus         = (int)$data->get('autoupdate');
        $autoupdateRegisterStatus = (int)$data->get('autoupdate_status');
        if ($data->get('updatesource') !== 'default' || $data->get('minimum_stability') !== '4') {
            // If we are not using Joomla Update Server or not using Stable Version disable the autoupdate
            if ($autoupdateRegisterStatus !== AutoupdateRegisterState::Unsubscribed->value) {
                $data->set('autoupdate_status', AutoupdateRegisterState::Unsubscribe->value);
            }
        } elseif ($autoupdateStatus === AutoupdateState::Enabled->value) {
            // If autoupdate is enabled and we are nor subscribed force subscription process
            if ($autoupdateRegisterStatus !== AutoupdateRegisterState::Subscribed->value) {
                $data->set('autoupdate_status', AutoupdateRegisterState::Subscribe->value);
            }
        } elseif ($autoupdateRegisterStatus !== AutoupdateRegisterState::Unsubscribed->value) {
            // If autoupdate is disabled and we are not unsubscribed force unsubscription process
            $data->set('autoupdate_status', AutoupdateRegisterState::Unsubscribe->value);
        }
        $table->params = $data->toString();
    }

    public function onExtensionAfterSave(\Joomla\CMS\Event\Model\AfterSaveEvent $event)
    {
        $context = $event->getArgument('context');
        $table   = $event->getArgument('subject');

        if ($context !== 'com_config.component') {
            return;
        }

        $data = new Registry($table->params);

        if (empty($data->get('update_token'))) {
            return;
        }

        $autoupdateRegisterStatus = AutoupdateRegisterState::from((int)$data->get('autoupdate_status'));
        // Check if action is required
        if ($autoupdateRegisterStatus === AutoupdateRegisterState::Unsubscribed
            || $autoupdateRegisterStatus === AutoupdateRegisterState::Subscribed
        ) {
            return;
        }

        $http = HttpFactory::getHttp();

        $url = self::autoupdateUrl;

        if ($autoupdateRegisterStatus === AutoupdateRegisterState::Subscribe) {
            $url .= '/register';
        } else {
            $url .= '/delete';
        }

        $requestData = [
            'url' => Uri::root(),
            'key' => $data->get('update_token'),
        ];

        // JHttp transport throws an exception when there's no response.
        try {
            $response = $http->post($url, json_encode($requestData), [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ], 20);
        } catch (\RuntimeException $e) {
            $response = null;
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        if ($response->getStatusCode() === 200) {
            $message = 'COM_JOOMLAUPDATE_AUTOUPDATE_REGISTER_SUCCESS';
            $data->set('autoupdate_status', AutoupdateRegisterState::Subscribed->value);

            if ($autoupdateRegisterStatus === AutoupdateRegisterState::Unsubscribe) {
                $message = 'COM_JOOMLAUPDATE_AUTOUPDATE_UNREGISTER_SUCCESS';
                $data->set('autoupdate_status', AutoupdateRegisterState::Unsubscribed->value);
            }

            $table->params = $data->toString();

            if (!$table->store()) {
                throw new \RuntimeException($table->getError());
            }

            Factory::getApplication()->enqueueMessage(Text::_($message), 'info');

            return;
        }

        $message = 'COM_JOOMLAUPDATE_AUTOUPDATE_REGISTER_ERROR';
        if ($autoupdateRegisterStatus === AutoupdateRegisterState::Unsubscribe) {
            $message = 'COM_JOOMLAUPDATE_AUTOUPDATE_UNREGISTER_ERROR';
        }

        $result = json_decode((string)$response->getBody(), true);
        Factory::getApplication()->enqueueMessage(Text::sprintf($message, $result['message'], $result['status']), 'error');
    }
}
