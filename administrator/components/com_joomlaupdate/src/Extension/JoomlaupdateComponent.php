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
use Joomla\CMS\User\UserHelper;

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
    public function prepareForm(Form $form, $data) : void
    {
        if ($form->getName() !== 'com_config.component')
        {
            return;
        }

        // We remove the token field, if no auto update is selected
        if (empty($data['autoupdate']) || !in_array($data['autoupdate'], ['minor', 'patch']))
        {
            $form->removeField('update_token');
        }
        // If we want automated updates, check if we have an old key to use otherwise generate a new one (don't use the submited one, if could be manipulated)
        else {
            $config = ComponentHelper::getParams('com_joomlaupdate');

            $token = $config->get('update_token');

            if (empty($token))
            {
                $token = UserHelper::genRandomPassword(40);
            }

            $form->setFieldAttribute('update_token', 'default', $token);
        }

        // Handle automated updates when form is submitted (but before it's saved)
        $input = Factory::getApplication()->getInput();

        if ($input->getMethod() === 'POST')
        {
            $this->manageAutoUpdate($data);
        }
    }

    /**
     * Decide if we subscribe or unsubscribe from automated updates
     *
     * @param array|object $data
     *
     * @return void
     */
    protected function manageAutoUpdate($data) {
        if (empty($data['autoupdate']) || !in_array($data['autoupdate'], ['minor', 'patch']))
        {
            $config = ComponentHelper::getParams('com_joomlaupdate');

            $token = $config->get('update_token');

            if ($token) {
                // @todo implement
                // $this->autoUpdateUnsubscribe($token);
            }

            return;
        }

        // @todo implement
        // $this->autoUpdateSubscribe($data);
    }
}
