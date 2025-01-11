<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_status
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\PrivacyStatus\Administrator\Dispatcher;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Component\Privacy\Administrator\Helper\PrivacyHelper;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_privacy_status
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dispatch()
    {
        // Only super user can view this data
        if (!$this->getApplication()->getIdentity()->authorise('core.admin')) {
            return;
        }

        parent::dispatch();
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $app                 = $this->getApplication();
        $privacyStatusHelper = $this->getHelperFactory()->getHelper('PrivacyStatusHelper');

        // Boot component to ensure HTML helpers are loaded
        $app->bootComponent('com_privacy');

        // Load the privacy component language file.
        $lang = $app->getLanguage();
        $lang->load('com_privacy', JPATH_ADMINISTRATOR)
            || $lang->load('com_privacy', JPATH_ADMINISTRATOR . '/components/com_privacy');

        $data['privacyPolicyInfo']            = $privacyStatusHelper->getPrivacyPolicyMenuStatus();
        $data['requestFormPublished']         = $privacyStatusHelper->getRequestFormMenuStatus();
        $data['privacyConsentPluginId']       = PrivacyHelper::getPrivacyConsentPluginId();
        $data['sendMailEnabled']              = (bool) $app->get('mailonline', 1);
        $data['numberOfUrgentRequests']       = $privacyStatusHelper->getUrgentRequestsNumber();
        $data['urgentRequestDays']            = (int) ComponentHelper::getParams('com_privacy')->get('notify', 14);
        $data['databaseConnectionEncryption'] = Factory::getContainer()->get(DatabaseInterface::class)->getConnectionEncryption();

        return $data;
    }
}
