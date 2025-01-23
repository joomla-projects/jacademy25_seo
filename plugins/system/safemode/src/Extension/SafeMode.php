<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  System.safemode
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\SafeMode\Extension;

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The class is responsible for managing safe mode in a Joomla application,
 * which involves disabling non-core plugins and extensions when safe mode is enabled.
 *
 * @since __DEPLOY_VERSION__
 */
final class SafeMode extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher  The event dispatcher.
     * @param   array                $config      An optional associative array of configuration settings.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(DispatcherInterface $dispatcher, array $config = [])
    {
        $options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
        $options['text_file'] = 'safemode.php';
        Log::addLogger($options);
        parent::__construct($dispatcher, $config);
    }

    /**
     * @inheritDoc
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterDispatch'           => 'onAfterDispatch',
            'onApplicationAfterSave'    => 'onApplicationAfterSave',
            'onApplicationBeforeSave'   => 'onApplicationBeforeSave',
            'onContentChangeState'      => 'onContentChangeState',
            'onExtensionAfterInstall'   => 'onExtensionAfterInstall',
            'onExtensionAfterSave'      => 'onExtensionAfterSave',
            'onExtensionAfterUninstall' => 'onExtensionAfterUninstall',
            'onExtensionAfterUpdate'    => 'onExtensionAfterUpdate',
            'onExtensionChangeState'    => 'onExtensionChangeState',
            'onJoomlaBeforeUpdate'      => 'onJoomlaBeforeUpdate',

        ];
    }

    /**
     * Listener for the `onAfterDispatch` event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onAfterDispatch()
    {
        $this->isSafeModeOn();
    }

    /**
     * Listener for the `onApplicationBeforeSave` event.
     *
     * This method is triggered before the application configuration is saved. It checks if Safe Mode is enabled
     * and ensures that Joomla's default User authentication plugin is enabled. If the authentication plugin is
     * not enabled, an error message is enqueued and the save operation is prevented.
     *
     * @param   Event  $event  The event to handle.
     *
     * @return  bool  True if the operation can proceed, false otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onApplicationBeforeSave(Event $event): bool
    {
        // Switch on Safe Mode
        if ($this->isSafeModeOn()) {
            return true;
        }

        $mvc   = $this->getApplication()->bootComponent('com_installer')->getMVCFactory();
        $model = $mvc->createModel('Manage', 'Administrator', ['ignore_request' => true]);

        // Check for Joomla's default User authentication plugin enabled.
        $model->setState('filter.type', 'plugin');
        $model->setState('filter.folder', 'authentication');
        $model->setState('filter.search', 'Authentication - Joomla');

        $authentication = $model->getItems();

        if ($authentication[0]->enabled === 0) {
            $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SAFEMODE_DEFAULT_AUTHENTICATION_PLUGIN_NOT_ENABLED'), 'error');
            $event->setArgument('result', [false]);
            return false;
        }

        return true;
    }

    /**
     * Listener for the `onApplicationAfterSave` event. This listener is used to enable or disable Safe Mode.
     *
     * When the event is triggered, it checks if Safe Mode is already on or off, and if it should be switched on or off.
     *
     * If Safe Mode is already on and the configuration is set to on, it does nothing.
     * If Safe Mode is already on and the configuration is set to off, it switches off Safe Mode.
     * If Safe Mode is already off and the configuration is set to on, it switches on Safe Mode.
     * If Safe Mode is already off and the configuration is set to off, it does nothing.
     *
     * @param Event $event The event to handle
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    public function onApplicationAfterSave(Event $event): void
    {

        $data = $event->getArgument('subject');

        // Safe Mode already on
        if ($this->isSafeModeOn() && $data->get('safemode')) {
            return;
        }

        // Switch off Safe Mode
        if ($this->isSafeModeOn() && !$data->get('safemode')) {
            $this->switchSafeModeOff();
            return;
        }

        // Switch on Safe Mode
        if (!$this->isSafeModeOn() && $data->get('safemode')) {
            $this->switchSafeModeOn();
            return;
        }

        // Safe Mode already off
        if (!$this->isSafeModeOn() && !$data->get('safemode')) {
            return;
        }
    }

    /**
     * Listens to the `onExtensionAfterInstall` event and enqueues a warning message
     * if a non-core extension is installed while safe mode is on.
     *
     * @param   Event  $event  The event to handle
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */


    public function onExtensionAfterInstall(Event $event): void
    {
        if (!$this->isSafeModeOn()) {
            return;
        }

        $installer = $event->getInstaller();
        $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SAFEMODE_INSTALLED_NONCORE_WHEN_SAFEMODEON'), 'warning');
        Log::add(Text::_('Installed') . ' ' . $installer->manifest->name, Log::WARNING, 'safemode');
    }

    /**
     * Listens to the `onExtensionAfterSave` event and prevents saving of non-core plugins when safe mode is on.
     *
     * @param   Event  $event  The event to handle
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onExtensionAfterSave(Event $event): void
    {
        // Plugins
        $data   = $event->getData();
        $upd    = $event->getIsNew();
        $table  = $event->getItem();

        if ($data['type'] !== 'plugin') {
            return;
        }

        if (!$this->isSafeModeOn()) {
            return;
        }

        if (!$table->enabled) {
            return;
        }
        // Safe Mode is On
        $coreExtensionIds = ExtensionHelper::getCoreExtensionIds();

        // Publishing a non-core plugin delist it
        if (!\in_array($table->extension_id, $coreExtensionIds)) {
            $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SAFEMODE_PUBLISH_NONCORE_WHEN_SAFEMODEON'), 'warning');
            $this->delistPlugin($table->extension_id);
        }
    }

    /**
     * Listens to the `onExtensionAfterUpdate` event and enqueues a warning message
     * if a non-core extension is updated while safe mode is on.
     *
     * @param   Event  $event  The event to handle
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */

    public function onExtensionAfterUpdate(Event $event): void
    {
        if (!$this->isSafeModeOn()) {
            return;
        }

        $installer = $event->getInstaller();
        $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SAFEMODE_UPDATED_NONCORE_WHEN_SAFEMODEON'), 'warning');
        Log::add(Text::_('Updated') . ' ' . $installer->manifest->name, Log::WARNING, 'safemode');
    }

    /**
     * Listens to the `onExtensionAfterUninstall` event and removes the plugin from the safe mode list if it was successfully uninstalled.
     *
     * @param   Event  $event  The event to handle
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */

    public function onExtensionAfterUninstall(Event $event): void
    {
        if (!$this->isSafeModeOn()) {
            return;
        }

        $eid       = $event->getEid();
        $result    = $event->getRemoved();
        $installer = $event->getInstaller();

        // If the process failed, ignore it
        if ($result === false) {
            return;
        }

        $this->delistPlugin($eid);
        Log::add(Text::_('Deleted') . ' ' . $installer->manifest->name, Log::WARNING, 'safemode');
    }

    /**
     * Handles the onContentChangeState event and prevents publishing of non-core plugins when safe mode is on.
     *
     * @param   Event  $event  The event to handle
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onContentChangeState(Event $event): void
    {
        // Plugins: Edit form
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();

        if (!$context === 'com_plugins.plugin') {
            return;
        }
        if (!$this->isSafeModeOn()) {
            return;
        }

        // Return when unpublishing
        if ($value === 0) {
            return;
        }

        // Get core exrension ids
        $coreExtensionIds = ExtensionHelper::getCoreExtensionIds();

        foreach ($pks as $id) {
            if (!\in_array($id, $coreExtensionIds)) {
                $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SAFEMODE_PUBLISH_NONCORE_WHEN_SAFEMODEON'), 'warning');
                $this->delistPlugin($id);
            }
        }
    }

    /**
     * Handles the onExtensionChangeState event and prevents publishing of non-core extensions when safe mode is on.
     *
     * @param   Event  $event  The event to handle
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onExtensionChangeState(Event $event): void
    {
        // Extensions: Manage view
        $pks     = $event->getPks();
        $value   = $event->getValue();

        if (!$this->isSafeModeOn()) {
            return;
        }

        // Safe Mode is On
        // Return when unpublishing
        if ($value === 0) {
            return;
        }

        // Check if there are plugins
        $arePlugins = false;
        foreach ($pks as $id) {
            if (!$this->isplugin($id)) {
                unset($pks[$id]);
                continue;
            }

            $arePlugins = true;
        }

        // Return when there are no plugins
        if (!$arePlugins) {
            return;
        }

        // Get core exrension ids
        $coreExtensionIds = ExtensionHelper::getCoreExtensionIds();

        foreach ($pks as $id) {
            if (!\in_array($id, $coreExtensionIds)) {
                $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SAFEMODE_PUBLISH_NONCORE_WHEN_SAFEMODEON'), 'warning');
                $this->delistPlugin($id);
            }
        }
    }

    /**
     * Listener for the `onJoomlaBeforeUpdate` event, which is triggered when Joomla is about to be updated.
     * If Safe Mode is on, it switches on Safe Mode.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onJoomlaBeforeUpdate(): void
    {
        // Switch on Safe Mode re-list
        if ($this->isSafeModeOn()) {
            $this->switchSafeModeOn();
        }

        return;
    }

    /**
     * Remove a plugin from the safe mode table.
     *
     * @param   int  $pk  The extension id to remove.
     *
     * @return  boolean  True if successful.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function delistPlugin(int $pk): bool
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__noncore_extensions_safemode'))
            ->where($db->quoteName('extension_id') . ' = :eid')
            ->bind(':eid', $pk, ParameterType::INTEGER);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\RuntimeException $e) {
            $this->getApplication()->enqueueMessage(('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return false;
        }

        return true;
    }

    /**
     * Returns an array of all non-core extension IDs that are currently in safe mode.
     *
     * @return  array  An array of extension IDs.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getList(): array
    {
        $plugins = [];
        $db      = $this->getDatabase();
        $query   = $db->getQuery(true);
        $query->select(
            [$db->quoteName('extension_id'),
            $db->quoteName('name')]
        )
            ->from($db->quoteName('#__noncore_extensions_safemode'));
        $db->setQuery($query);

        try {
            $plugins = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $this->getApplication()->enqueueMessage(('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return [];
        }

        return $plugins;
    }

    /**
     * Disable safe mode (Enable all previously disabled non-core plugins).
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function switchSafeModeOff(): void
    {
        // Check if the user is authorized to do this.
        if (!$this->getApplication()->getIdentity()->authorise('core.admin')) {
            $this->getApplication()->enqueueMessage(('JERROR_ALERTNOAUTHOR'), 'error');

            return;
        }

        /** @var ManageModel $model */
        $mvc   = $this->getApplication()->bootComponent('com_installer')->getMVCFactory();
        $model = $mvc->createModel('Manage', 'Administrator', ['ignore_request' => true]);

        $pluginObject = $this->getList();

        foreach ($pluginObject as $plugin) {
            $return = $model->publish($plugin->extension_id, 1);
            Log::add(Text::_('published') . ' ' . $plugin->name, Log::WARNING, 'safemode');
        }

        $this->getApplication()->getMessageQueue(true);
        $this->getApplication()->enqueueMessage(Text::_(('PLG_SYSTEM_SAFEMODE_OFF'), 'warning'));

        Log::add(Text::_('Switch SafeMode OFF'), Log::WARNING, 'safemode');
    }

    /**
     * Disable all non-core plugins enabled (Safe mode).
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function switchSafeModeOn(): void
    {
        /** @var ManageModel $model */
        $mvc   = $this->getApplication()->bootComponent('com_installer')->getMVCFactory();
        $model = $mvc->createModel('Manage', 'Administrator', ['ignore_request' => true]);

        $model->setState('filter.type', 'plugin');
        $model->setState('filter.status', '1');
        $model->setState('filter.core', '0');

        $pluginObject = $model->getItems();

        foreach ($pluginObject as $plugin) {
            $return = $model->publish($plugin->extension_id, 0);

            if ($return) {
                $this->setSafeOn($plugin);
            }
        }
        Log::add(Text::_('Switch SafeMode ON'), Log::WARNING, 'safemode');
    }

    /**
     * Save a plugin in the safe mode table.
     *
     * @param   object  $plugin  The plugin object to safe.
     *
     * @return  boolean  True if successful.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setSafeOn(object $plugin): bool
    {
        $db    = $this->getDatabase();
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__noncore_extensions_safemode'))
            ->columns(
                [
                    $db->quoteName('extension_id'),
                    $db->quoteName('name'),
                    $db->quoteName('type'),
                    $db->quoteName('element'),
                    $db->quoteName('time'),
                ]
            )
            ->values(':extension_id, :name, :type, :element, :time')
            ->bind(':extension_id', $plugin->extension_id, ParameterType::INTEGER)
            ->bind(':name', $plugin->name)
            ->bind(':type', $plugin->type)
            ->bind(':element', $plugin->element)
            ->bind(':time', $now);
        $db->setQuery($query);
        Log::add(Text::_('unpublished') . ' ' . $plugin->name, Log::WARNING, 'safemode');
        try {
            $db->execute();
        } catch (\RuntimeException $e) {
            $this->getApplication()->enqueueMessage(('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return false;
        }

        return true;
    }

    /**
     * Checks if safe mode is enabled.
     *
     * If safe mode is on, a warning message is enqueued.
     *
     * @return  boolean  True if safe mode is on, otherwise false.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isSafeModeOn(): bool
    {
        $config = $this->getApplication()->getConfig();
        $return = false;

        if ($config->get('safemode', false)) {
            $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SAFEMODE_ON'), 'warning');
            $return = true;
        }

        return $return;
    }

    /**
     * Checks if an extension with the given ID is a plugin.
     *
     * @param   int  $id  The ID of the extension to check.
     *
     * @return  bool  True if the extension is a plugin, otherwise false.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isPlugin(int $id): bool
    {
        /** @var ManageModel $model */
        $mvc   = $this->getApplication()->bootComponent('com_installer')->getMVCFactory();
        $model = $mvc->createModel('Manage', 'Administrator', ['ignore_request' => true]);
        $model->setState('filter.type', 'plugin');
        $model->setState('filter.search', 'id:' . $id);

        $return = false;

        if ($model->getItems()) {
            $return = true;
        }

        return $return;
    }
}
