<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

trait InstallerScriptTrait
{
    /**
     * The extension name. This should be set in the installer script.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $extension;

    /**
     * Minimum PHP version required to install the extension
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $minimumPhp;

    /**
     * Minimum Joomla! version required to install the extension
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $minimumJoomla;

    /**
     * Allow downgrades of your extension
     *
     * Use at your own risk as if there is a change in functionality people may wish to downgrade.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $allowDowngrades = false;

    /**
     * A list of files to be deleted
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $deleteFiles = [];

    /**
     * A list of folders to be deleted
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $deleteFolders = [];

    /**
     * Function called after the extension is installed.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    /**
     * Function called after the extension is updated.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    /**
     * Function called after the extension is uninstalled.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    /**
     * Function called before extension installation/update/removal procedure commences.
     *
     * @param   string            $type     The type of change (install or discover_install, update, uninstall)
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        $this->extension = $adapter->getName();

        if (!$this->checkCompatibility($type, $adapter)) {
            return false;
        }

        if (!$this->checkDowngrade($type, $adapter)) {
            return false;
        }
        return true;
    }

    /**
     * Function called after extension installation/update/removal procedure commences.
     *
     * @param   string            $type     The type of change (install or discover_install, update, uninstall)
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    public function postflight(string $type, InstallerAdapter $adapter): bool
    {

        $this->removeFiles();

        return true;
    }

    /**
     * Check if the extension passes the minimum requirements to be installed
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function checkCompatibility(string $type, InstallerAdapter $adapter): bool
    {
        // Check for the minimum PHP version before continuing
        if (!empty($this->minimumPhp) && version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPhp), Log::WARNING, 'jerror');

            return false;
        }

        // Check for the minimum Joomla version before continuing
        if (!empty($this->minimumJoomla) && version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomla), Log::WARNING, 'jerror');

            return false;
        }

        return true;
    }

    /**
     * Check if the extension is allowed to be downgraded
     *
     * @return  boolean  False when downgrade not allowed and new version is lower than old version otherwise true
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function checkDowngrade(string $type, InstallerAdapter $adapter): bool
    {
        if ($type !== 'update' || $this->allowDowngrades) {
            return true;
        }

        $oldManifest = $this->getOldManifest($adapter);

        if ($oldManifest !== null && $oldManifest->version && version_compare($oldManifest->version, $adapter->getManifest()->version, '>')) {
            Log::add(Text::_('JLIB_INSTALLER_ERROR_DOWNGRADE'), Log::WARNING, 'jerror');

            return false;
        }

        return true;
    }

    /**
     * Returns the manifest file if it exists or null
     *
     * @param InstallerAdapter $adapter
     *
     * @return SimpleXMLElement|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getOldManifest(InstallerAdapter $adapter): ?\SimpleXMLElement
    {
        $client = ApplicationHelper::getClientInfo(1);

        $pathname = 'extension_' . ($client ? $client->name : 'root');

        $manifestPath = $adapter->getParent()->getPath($pathname) . '/' . $adapter->getParent()->getPath('manifest');

        return is_file($manifestPath) ? $adapter->getParent()->isManifest($manifestPath) : null;
    }

    /**
     * Remove the files and folders in the given array from
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function removeFiles(): void
    {
        if (!empty($this->deleteFiles)) {
            foreach ($this->deleteFiles as $file) {
                if (is_file(Path::check(JPATH_ROOT . $file)) && !File::delete(JPATH_ROOT . $file)) {
                    Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $file));
                }
            }
        }

        if (!empty($this->deleteFolders)) {
            foreach ($this->deleteFolders as $folder) {
                if (is_dir(Path::check(JPATH_ROOT . $folder)) && !Folder::delete(JPATH_ROOT . $folder)) {
                    Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $folder));
                }
            }
        }
    }

    /**
     * Creates the dashboard menu module
     *
     * @param string $dashboard The name of the dashboard
     * @param string $preset    The name of the menu preset
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    protected function addDashboardMenuModule(string $dashboard, string $preset)
    {
        $model  = Factory::getApplication()->bootComponent('com_modules')->getMVCFactory()->createModel('Module', 'Administrator', ['ignore_request' => true]);
        $module = [
            'id'         => 0,
            'asset_id'   => 0,
            'language'   => '*',
            'note'       => '',
            'published'  => 1,
            'assignment' => 0,
            'client_id'  => 1,
            'showtitle'  => 0,
            'content'    => '',
            'module'     => 'mod_submenu',
            'position'   => 'cpanel-' . $dashboard,
        ];

        // Try to get a translated module title, otherwise fall back to a fixed string.
        $titleKey         = strtoupper('COM_' . $this->extension . '_DASHBOARD_' . $dashboard . '_TITLE');
        $title            = Text::_($titleKey);
        $module['title']  = ($title === $titleKey) ? ucfirst($dashboard) . ' Dashboard' : $title;

        $module['access'] = (int) Factory::getApplication()->get('access', 1);
        $module['params'] = [
            'menutype' => '*',
            'preset'   => $preset,
            'style'    => 'System-none',
        ];

        if (!$model->save($module)) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_INSTALLER_ERROR_COMP_INSTALL_FAILED_TO_CREATE_DASHBOARD', $model->getError()));
        }
    }
}
