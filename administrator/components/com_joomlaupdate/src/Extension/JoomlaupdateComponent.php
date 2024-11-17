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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Version;
use Joomla\Http\TransportInterface;
use Joomla\Registry\Registry;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

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
        $table = $event->getArgument('subject');

        if ($context !== 'com_config.component') {
            return;
        }

        $data = new Registry($table->params);

        if ($data->get('updatesource') !== 'default' || $data->get('minimum_stability') !== '4') {
            if ((int)$data->get('autoupdate_status') === 1) {
                $data->set('autoupdate_status', 2);
            } else {
                $data->set('autoupdate_status', 1);
            }
            $table->params = $data->toString();
        } else if ((int) $data->get('autoupdate') === 0) {
            if ((int) $data->get('autoupdate_status') === 1) {
                $data->set('autoupdate_status', 2);
            } else {
                $data->set('autoupdate_status', 1);
            }
            $table->params = $data->toString();
        } else {
            $data->set('autoupdate_status', 0);
            $table->params = $data->toString();
        }
    }

    public function onExtensionAfterSave(\Joomla\CMS\Event\Model\AfterSaveEvent $event)
    {
        $context = $event->getArgument('context');
        $table = $event->getArgument('subject');

        if ($context !== 'com_config.component') {
            return;
        }

        $data = new Registry($table->params);

        if (empty($data->get('update_token'))) {
            return;
        }

        // We are already unsubscribed
        if ($data->get('autoupdate_status') === 2) {
            return;
        }

        $register = true;
        if ((int)$data->get('autoupdate') !== 1 || (int)$data->get('autoupdate_status') !== 0) {
            $register = false;
        }

        $http = HttpFactory::getHttp();

        $url = 'https://autoupdate.joomla.org/api/v1';

        if ($register) {
            $url .= '/register';
        } else {
            $url .= '/delete';
        }

        $requestData = [
            'url' => Route::link('site', 'index.php', false, Route::TLS_IGNORE, true),
            'key' => $data->get('update_token'),
        ];

        // JHttp transport throws an exception when there's no response.
        try {
            $response = $http->post($url, json_encode($requestData), [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ], 20);
        } catch (\RuntimeException $e) {
            $response = null;
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        if ($response->getStatusCode() === 200) {
            $message = 'COM_JOOMLAUPDATE_AUTOUPDATE_REGISTER_SUCCESS';
            if (!$register) {
                $message = 'COM_JOOMLAUPDATE_AUTOUPDATE_UNREGISTER_SUCCESS';
            }

            Factory::getApplication()->enqueueMessage(Text::_($message), 'info');

            return;
        }

        $message = 'COM_JOOMLAUPDATE_AUTOUPDATE_REGISTER_ERROR';
        if (!$register) {
            $message = 'COM_JOOMLAUPDATE_AUTOUPDATE_UNREGISTER_ERROR';
        }

        $result = json_decode((string)$response->getBody(), true);
        Factory::getApplication()->enqueueMessage(Text::sprintf($message, $result['message'], $result['status']), 'error');
    }
}
