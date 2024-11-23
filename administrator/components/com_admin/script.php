<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Script file of Joomla CMS
 *
 * @since  1.6.4
 */
class JoomlaInstallerScript
{
    /**
     * The Joomla Version we are updating from
     *
     * @var    string
     * @since  3.7
     */
    protected $fromVersion = null;

    /**
     * Callback for collecting errors. Like function(string $context, \Throwable $error){};
     *
     * @var callable
     *
     * @since  4.4.0
     */
    protected $errorCollector;

    /**
     * Set the callback for collecting errors.
     *
     * @param   callable  $callback  The callback Like function(string $context, \Throwable $error){};
     *
     * @return  void
     *
     * @since  4.4.0
     */
    public function setErrorCollector(callable $callback)
    {
        $this->errorCollector = $callback;
    }

    /**
     * Collect errors.
     *
     * @param  string      $context  A context/place where error happened
     * @param  \Throwable  $error    The error that occurred
     *
     * @return  void
     *
     * @since  4.4.0
     */
    protected function collectError(string $context, \Throwable $error)
    {
        // The errorCollector are required
        // However when someone already running the script manually the code may fail.
        if ($this->errorCollector) {
            \call_user_func($this->errorCollector, $context, $error);
        } else {
            Log::add($error->getMessage(), Log::ERROR, 'Update');
        }
    }

    /**
     * Function to act prior to installation process begins
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   3.7.0
     */
    public function preflight($action, $installer)
    {
        if ($action === 'update') {
            // Get the version we are updating from
            if (!empty($installer->extension->manifest_cache)) {
                $manifestValues = json_decode($installer->extension->manifest_cache, true);

                if (\array_key_exists('version', $manifestValues)) {
                    $this->fromVersion = $manifestValues['version'];

                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Method to update Joomla!
     *
     * @param   Installer  $installer  The class calling this method
     *
     * @return  void
     */
    public function update($installer)
    {
        // Uninstall extensions before removing their files and folders
        try {
            Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_UNINSTALL_EXTENSIONS'), Log::INFO, 'Update');
            $this->uninstallExtensions();
        } catch (\Throwable $e) {
            $this->collectError('uninstallExtensions', $e);
        }

        // Remove old files
        try {
            Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_DELETE_FILES'), Log::INFO, 'Update');
            $this->deleteUnexistingFiles();
        } catch (\Throwable $e) {
            $this->collectError('deleteUnexistingFiles', $e);
        }

        // Further update
        try {
            $this->updateManifestCaches();
            $this->updateDatabase();
            $this->updateAssets($installer);
            $this->clearStatsCache();
        } catch (\Throwable $e) {
            $this->collectError('Further update', $e);
        }

        // Clean cache
        try {
            $this->cleanJoomlaCache();
        } catch (\Throwable $e) {
            $this->collectError('cleanJoomlaCache', $e);
        }
    }

    /**
     * Method to clear our stats plugin cache to ensure we get fresh data on Joomla Update
     *
     * @return  void
     *
     * @since   3.5
     */
    protected function clearStatsCache()
    {
        $db = Factory::getDbo();

        try {
            // Get the params for the stats plugin
            $params = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
            )->loadResult();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        $params = json_decode($params, true);

        // Reset the last run parameter
        if (isset($params['lastrun'])) {
            $params['lastrun'] = '';
        }

        $params = json_encode($params);

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }
    }

    /**
     * Method to update Database
     *
     * @return  void
     */
    protected function updateDatabase()
    {
        if (Factory::getDbo()->getServerType() === 'mysql') {
            $this->updateDatabaseMysql();
        }
    }

    /**
     * Method to update MySQL Database
     *
     * @return  void
     */
    protected function updateDatabaseMysql()
    {
        $db = Factory::getDbo();

        $db->setQuery('SHOW ENGINES');

        try {
            $results = $db->loadObjectList();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        foreach ($results as $result) {
            if ($result->Support != 'DEFAULT') {
                continue;
            }

            $db->setQuery('ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine);

            try {
                $db->execute();
            } catch (Exception $e) {
                $this->collectError(__METHOD__, $e);

                return;
            }

            break;
        }
    }

    /**
     * Uninstall extensions and optionally migrate their parameters when
     * updating from a version older than 6.0.0.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    protected function uninstallExtensions()
    {
        // Don't uninstall extensions when not updating from a version older than 6.0.0
        if (empty($this->fromVersion) || version_compare($this->fromVersion, '6.0.0', 'ge')) {
            return true;
        }

        $extensions = [
            /**
             * Define here the extensions to be uninstalled and optionally migrated on update.
             * For each extension, specify an associative array with following elements (key => value):
             * 'type'         => Field `type` in the `#__extensions` table
             * 'element'      => Field `element` in the `#__extensions` table
             * 'folder'       => Field `folder` in the `#__extensions` table
             * 'client_id'    => Field `client_id` in the `#__extensions` table
             * 'pre_function' => Name of an optional migration function to be called before
             *                   uninstalling, `null` if not used.
             * Examples:
             * ['type' => 'plugin', 'element' => 'demotasks', 'folder' => 'task', 'client_id' => 0, 'pre_function' => null],
             * ['type' => 'plugin', 'element' => 'compat', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateCompatPlugin'],
             */
        ];

        $db = Factory::getDbo();

        foreach ($extensions as $extension) {
            $row = $db->setQuery(
                $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote($extension['type']))
                    ->where($db->quoteName('element') . ' = ' . $db->quote($extension['element']))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote($extension['folder']))
                    ->where($db->quoteName('client_id') . ' = ' . $db->quote($extension['client_id']))
            )->loadObject();

            // Skip migrating and uninstalling if the extension doesn't exist
            if (!$row) {
                continue;
            }

            // If there is a function for migration to be called before uninstalling, call it
            if ($extension['pre_function'] && method_exists($this, $extension['pre_function'])) {
                $this->{$extension['pre_function']}($row);
            }

            try {
                $db->transactionStart();

                // Unlock and unprotect the plugin so we can uninstall it
                $db->setQuery(
                    $db->getQuery(true)
                        ->update($db->quoteName('#__extensions'))
                        ->set($db->quoteName('locked') . ' = 0')
                        ->set($db->quoteName('protected') . ' = 0')
                        ->where($db->quoteName('extension_id') . ' = :extension_id')
                        ->bind(':extension_id', $row->extension_id, ParameterType::INTEGER)
                )->execute();

                // Uninstall the plugin
                $installer = new Installer();
                $installer->setDatabase($db);
                $installer->uninstall($extension['type'], $row->extension_id);

                $db->transactionCommit();
            } catch (\Exception $e) {
                $db->transactionRollback();
                throw $e;
            }
        }
    }

    /**
     * Update the manifest caches
     *
     * @return  void
     */
    protected function updateManifestCaches()
    {
        $extensions = ExtensionHelper::getCoreExtensions();

        // Attempt to refresh manifest caches
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__extensions');

        foreach ($extensions as $extension) {
            $query->where(
                'type=' . $db->quote($extension[0])
                . ' AND element=' . $db->quote($extension[1])
                . ' AND folder=' . $db->quote($extension[2])
                . ' AND client_id=' . $extension[3],
                'OR'
            );
        }

        $db->setQuery($query);

        try {
            $extensions = $db->loadObjectList();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        $installer = new Installer();
        $installer->setDatabase($db);

        foreach ($extensions as $extension) {
            if (!$installer->refreshManifestCache($extension->extension_id)) {
                $this->collectError(
                    __METHOD__,
                    new \Exception(\sprintf(
                        'Error on updating manifest cache: (type, element, folder, client) = (%s, %s, %s, %s)',
                        $extension->type,
                        $extension->element,
                        $extension->name,
                        $extension->client_id
                    ))
                );
            }
        }
    }

    /**
     * Delete files that should not exist
     *
     * @param bool  $dryRun          If set to true, will not actually delete files, but just report their status for use in CLI
     * @param bool  $suppressOutput   Set to true to suppress echoing any errors, and just return the $status array
     *
     * @return  array
     */
    public function deleteUnexistingFiles($dryRun = false, $suppressOutput = false)
    {
        $status = [
            'files_exist'     => [],
            'folders_exist'   => [],
            'files_deleted'   => [],
            'folders_deleted' => [],
            'files_errors'    => [],
            'folders_errors'  => [],
        ];

        $files = [
            // From 5.x to 6.0
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-03-11.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-03-17.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-07-12.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-07-25.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-07-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-21.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-26.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-30.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-09-02.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-09-06.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-09-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-09-11.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2023-11-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-01-04.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-02-10.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-02-24.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-02-25.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-03-08.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-03-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.1-2024-04-18.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.0-2024-07-02.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.0-2024-07-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.0-2024-08-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.0-2024-09-17.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.2-2024-09-24.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.0-2024-10-13.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-03-11.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-03-17.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-07-12.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-07-25.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-07-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-21.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-26.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-30.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-09-02.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-09-06.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-09-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-09-11.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2023-11-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-02-10.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-02-24.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-02-25.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-03-08.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-03-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.1-2024-04-18.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.0-2024-07-02.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.0-2024-07-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.0-2024-08-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.0-2024-09-17.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.2-2024-09-24.sql',
            '/libraries/src/Application/BaseApplication.php',
            '/libraries/src/Application/CLI/CliInput.php',
            '/libraries/src/Application/CLI/CliOutput.php',
            '/libraries/src/Application/CLI/ColorStyle.php',
            '/libraries/src/Application/CLI/Output/Processor/ColorProcessor.php',
            '/libraries/src/Application/CLI/Output/Processor/ProcessorInterface.php',
            '/libraries/src/Application/CLI/Output/Stdout.php',
            '/libraries/src/Application/CLI/Output/Xml.php',
            '/libraries/src/Application/CliApplication.php',
            '/libraries/src/Input/Cookie.php',
            '/libraries/src/Input/Files.php',
            '/libraries/src/Input/Input.php',
            '/libraries/src/Input/Json.php',
        ];

        $folders = [
            // From 5.x to 6.0
            '/libraries/src/Application/CLI/Output/Processor',
            '/libraries/src/Application/CLI/Output',
            '/libraries/src/Application/CLI',
        ];

        $status['files_checked']   = $files;
        $status['folders_checked'] = $folders;

        foreach ($files as $file) {
            if (is_file(JPATH_ROOT . $file)) {
                $status['files_exist'][] = $file;

                if ($dryRun === false) {
                    if (File::delete(JPATH_ROOT . $file)) {
                        $status['files_deleted'][] = $file;
                    } else {
                        $status['files_errors'][] = \sprintf('Error on deleting file or folder %s', $file);
                    }
                }
            }
        }

        foreach ($folders as $folder) {
            if (is_dir(JPATH_ROOT . $folder)) {
                $status['folders_exist'][] = $folder;

                if ($dryRun === false) {
                    if (Folder::delete(JPATH_ROOT . $folder)) {
                        $status['folders_deleted'][] = $folder;
                    } else {
                        $status['folders_errors'][] = \sprintf('Error on deleting file or folder %s', $folder);
                    }
                }
            }
        }

        $this->fixFilenameCasing();

        /**
         * Fix filesystem permissions when updating a new 5.2.0 installation.
         *
         * @todo: Remove in 6.0
         */
        $this->fixFilesystemPermissions();

        if ($suppressOutput === false && \count($status['folders_errors'])) {
            echo implode('<br>', $status['folders_errors']);
        }

        if ($suppressOutput === false && \count($status['files_errors'])) {
            echo implode('<br>', $status['files_errors']);
        }

        return $status;
    }

    /**
     * Method to create assets for newly installed components
     *
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean
     *
     * @since   3.2
     */
    public function updateAssets($installer)
    {
        // List all components added since 6.0
        $newComponents = [
            // Components to be added here
        ];

        foreach ($newComponents as $component) {
            /** @var \Joomla\CMS\Table\Asset $asset */
            $asset = Table::getInstance('Asset');

            if ($asset->loadByName($component)) {
                continue;
            }

            $asset->name      = $component;
            $asset->parent_id = 1;
            $asset->rules     = '{}';
            $asset->title     = $component;
            $asset->setLocation(1, 'last-child');

            if (!$asset->store()) {
                $this->collectError(__METHOD__, new \Exception($asset->getError(true)));

                // Install failed, roll back changes
                $installer->abort(Text::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $asset->getError(true)));

                return false;
            }
        }

        return true;
    }

    /**
     * This method clean the Joomla Cache using the method `clean` from the com_cache model
     *
     * @return  void
     *
     * @since   3.5.1
     */
    private function cleanJoomlaCache()
    {
        /** @var \Joomla\Component\Cache\Administrator\Model\CacheModel $model */
        $model = Factory::getApplication()->bootComponent('com_cache')->getMVCFactory()
            ->createModel('Cache', 'Administrator', ['ignore_request' => true]);

        // Clean frontend cache
        $model->clean();

        // Clean admin cache
        $model->setState('client_id', 1);
        $model->clean();
    }

    /**
     * Called after any type of action
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   4.0.0
     */
    public function postflight($action, $installer)
    {
        if ($action !== 'update') {
            return true;
        }

        if (empty($this->fromVersion) || version_compare($this->fromVersion, '6.0.0', 'ge')) {
            return true;
        }

        // Add here code which shall be executed only when updating from an older version than 6.0.0

        // Refresh versionable assets cache.
        Factory::getApplication()->flushAssets();

        return true;
    }

    /**
     * Renames or removes incorrectly cased files.
     *
     * @return  void
     *
     * @since   3.9.25
     */
    protected function fixFilenameCasing()
    {
        $files = [
            // From 5.x to 6.0
        ];

        foreach ($files as $old => $expected) {
            $oldRealpath = realpath(JPATH_ROOT . $old);

            // On Unix without incorrectly cased file.
            if ($oldRealpath === false) {
                continue;
            }

            $oldBasename      = basename($oldRealpath);
            $newRealpath      = realpath(JPATH_ROOT . $expected);
            $newBasename      = basename($newRealpath);
            $expectedBasename = basename($expected);

            // On Windows or Unix with only the incorrectly cased file.
            if ($newBasename !== $expectedBasename) {
                // Rename the file.
                File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);

                continue;
            }

            // There might still be an incorrectly cased file on other OS than Windows.
            if ($oldBasename === basename($old)) {
                // Check if case-insensitive file system, eg on OSX.
                if (fileinode($oldRealpath) === fileinode($newRealpath)) {
                    // Check deeper because even realpath or glob might not return the actual case.
                    if (!\in_array($expectedBasename, scandir(\dirname($newRealpath)))) {
                        // Rename the file.
                        File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                        File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);
                    }
                } else {
                    // On Unix with both files: Delete the incorrectly cased file.
                    if (is_file(JPATH_ROOT . $old)) {
                        File::delete(JPATH_ROOT . $old);
                    }
                }
            }
        }
    }

    /**
     * Fix filesystem permissions when updating a new 5.2.0 installation.
     *
     * @return  void
     *
     * @since   5.2.2
     *
     * @todo    6.0 Remove this method
     *
     * @deprecated  5.2.2 will be removed in 6.0 without replacement
     */
    protected function fixFilesystemPermissions()
    {
        // Don't do anything if not updating from a 5.2.0 or 5.2.1
        if (
            empty($this->fromVersion)
            || version_compare($this->fromVersion, '5.2.0', 'lt')
            || version_compare($this->fromVersion, '5.2.1', 'gt')
        ) {
            return;
        }

        // First check tmp folder if it has mode 777
        if (decoct(fileperms(JPATH_ROOT . '/tmp') & 0777) === '777') {
            // We are either on Windows where folders always have 777, or we have to fix permissions
            @chmod(JPATH_ROOT . '/tmp', 0755);
            clearstatcache(true, JPATH_ROOT . '/tmp');
        }

        // Check tmp folder again if it still has mode 777
        if (decoct(fileperms(JPATH_ROOT . '/tmp') & 0777) === '777') {
            // We are on Windows or chmod has no effect
            return;
        }

        try {
            // Using hard-coded string because a new language string would not be available in all cases
            Log::add('Fixing permissions for files and folders.', Log::INFO, 'Update');
        } catch (\RuntimeException $exception) {
            // Informational log only
        }

        $files = [
            '/htaccess.txt',
            '/index.php',
            '/libraries/.htaccess',
            '/libraries/vendor/jfcherng/php-diff/.phpstorm.meta.php',
            '/libraries/vendor/joomla/http/.drone.jsonnet',
            '/libraries/vendor/joomla/http/.drone.yml',
            '/libraries/vendor/joomla/oauth1/.drone.jsonnet',
            '/libraries/vendor/joomla/oauth1/.drone.yml',
            '/libraries/vendor/joomla/oauth2/.drone.jsonnet',
            '/libraries/vendor/joomla/oauth2/.drone.yml',
            '/libraries/vendor/joomla/router/.drone.jsonnet',
            '/libraries/vendor/joomla/router/.drone.yml',
            '/libraries/vendor/joomla/string/.drone.jsonnet',
            '/libraries/vendor/joomla/string/.drone.yml',
            '/libraries/vendor/joomla/uri/.drone.jsonnet',
            '/libraries/vendor/joomla/uri/.drone.yml',
            '/libraries/vendor/joomla/utilities/.drone.jsonnet',
            '/libraries/vendor/joomla/utilities/.drone.yml',
            '/LICENSE.txt',
            '/README.txt',
            '/robots.txt',
            '/robots.txt.dist',
            '/tmp/index.html',
            '/web.config.txt',
        ];

        $folders = [
            '/administrator',
            '/administrator/cache',
            '/administrator/components',
            '/administrator/help',
            '/administrator/help/en-GB',
            '/administrator/includes',
            '/administrator/language',
            '/administrator/language/en-GB',
            '/administrator/language/overrides',
            '/administrator/logs',
            '/administrator/manifests',
            '/administrator/manifests/files',
            '/administrator/manifests/libraries',
            '/administrator/manifests/packages',
            '/administrator/modules',
            '/administrator/templates',
            '/api',
            '/api/components',
            '/api/includes',
            '/api/language',
            '/api/language/en-GB',
            '/cache',
            '/cli',
            '/components',
            '/images',
            '/images/banners',
            '/images/headers',
            '/images/sampledata',
            '/images/sampledata/cassiopeia',
            '/includes',
            '/language',
            '/language/en-GB',
            '/language/overrides',
            '/layouts',
            '/layouts/chromes',
            '/layouts/libraries',
            '/layouts/libraries/html',
            '/layouts/libraries/html/bootstrap',
            '/layouts/libraries/html/bootstrap/modal',
            '/layouts/libraries/html/bootstrap/tab',
            '/libraries',
            '/libraries/php-encryption',
            '/libraries/phpass',
            '/media',
            '/media/cache',
            '/media/templates',
            '/media/templates/administrator',
            '/media/templates/site',
            '/media/vendor',
            '/modules',
            '/plugins',
            '/templates',
        ];

        $foldersRecursive = [
            '/administrator/components/com_actionlogs',
            '/administrator/components/com_admin',
            '/administrator/components/com_ajax',
            '/administrator/components/com_associations',
            '/administrator/components/com_banners',
            '/administrator/components/com_cache',
            '/administrator/components/com_categories',
            '/administrator/components/com_checkin',
            '/administrator/components/com_config',
            '/administrator/components/com_contact',
            '/administrator/components/com_content',
            '/administrator/components/com_contenthistory',
            '/administrator/components/com_cpanel',
            '/administrator/components/com_fields',
            '/administrator/components/com_finder',
            '/administrator/components/com_guidedtours',
            '/administrator/components/com_installer',
            '/administrator/components/com_joomlaupdate',
            '/administrator/components/com_languages',
            '/administrator/components/com_login',
            '/administrator/components/com_mails',
            '/administrator/components/com_media',
            '/administrator/components/com_menus',
            '/administrator/components/com_messages',
            '/administrator/components/com_modules',
            '/administrator/components/com_newsfeeds',
            '/administrator/components/com_plugins',
            '/administrator/components/com_postinstall',
            '/administrator/components/com_privacy',
            '/administrator/components/com_redirect',
            '/administrator/components/com_scheduler',
            '/administrator/components/com_tags',
            '/administrator/components/com_templates',
            '/administrator/components/com_users',
            '/administrator/components/com_workflow',
            '/administrator/components/com_wrapper',
            '/administrator/modules/mod_custom',
            '/administrator/modules/mod_feed',
            '/administrator/modules/mod_frontend',
            '/administrator/modules/mod_guidedtours',
            '/administrator/modules/mod_latest',
            '/administrator/modules/mod_latestactions',
            '/administrator/modules/mod_logged',
            '/administrator/modules/mod_login',
            '/administrator/modules/mod_loginsupport',
            '/administrator/modules/mod_menu',
            '/administrator/modules/mod_messages',
            '/administrator/modules/mod_multilangstatus',
            '/administrator/modules/mod_popular',
            '/administrator/modules/mod_post_installation_messages',
            '/administrator/modules/mod_privacy_dashboard',
            '/administrator/modules/mod_privacy_status',
            '/administrator/modules/mod_quickicon',
            '/administrator/modules/mod_sampledata',
            '/administrator/modules/mod_stats_admin',
            '/administrator/modules/mod_submenu',
            '/administrator/modules/mod_title',
            '/administrator/modules/mod_toolbar',
            '/administrator/modules/mod_user',
            '/administrator/modules/mod_version',
            '/administrator/templates/atum',
            '/administrator/templates/system',
            '/api/components/com_banners',
            '/api/components/com_categories',
            '/api/components/com_config',
            '/api/components/com_contact',
            '/api/components/com_content',
            '/api/components/com_contenthistory',
            '/api/components/com_fields',
            '/api/components/com_installer',
            '/api/components/com_languages',
            '/api/components/com_media',
            '/api/components/com_menus',
            '/api/components/com_messages',
            '/api/components/com_modules',
            '/api/components/com_newsfeeds',
            '/api/components/com_plugins',
            '/api/components/com_privacy',
            '/api/components/com_redirect',
            '/api/components/com_tags',
            '/api/components/com_templates',
            '/api/components/com_users',
            '/components/com_ajax',
            '/components/com_banners',
            '/components/com_config',
            '/components/com_contact',
            '/components/com_content',
            '/components/com_contenthistory',
            '/components/com_fields',
            '/components/com_finder',
            '/components/com_media',
            '/components/com_menus',
            '/components/com_modules',
            '/components/com_newsfeeds',
            '/components/com_privacy',
            '/components/com_tags',
            '/components/com_users',
            '/components/com_wrapper',
            '/layouts/joomla',
            '/layouts/plugins',
            '/libraries/src',
            '/libraries/vendor',
            '/media/com_actionlogs',
            '/media/com_admin',
            '/media/com_associations',
            '/media/com_banners',
            '/media/com_cache',
            '/media/com_categories',
            '/media/com_config',
            '/media/com_contact',
            '/media/com_content',
            '/media/com_contenthistory',
            '/media/com_cpanel',
            '/media/com_fields',
            '/media/com_finder',
            '/media/com_guidedtours',
            '/media/com_installer',
            '/media/com_joomlaupdate',
            '/media/com_languages',
            '/media/com_mails',
            '/media/com_media',
            '/media/com_menus',
            '/media/com_modules',
            '/media/com_scheduler',
            '/media/com_tags',
            '/media/com_templates',
            '/media/com_users',
            '/media/com_workflow',
            '/media/com_wrapper',
            '/media/layouts',
            '/media/legacy',
            '/media/mailto',
            '/media/mod_articles',
            '/media/mod_articles_news',
            '/media/mod_languages',
            '/media/mod_login',
            '/media/mod_menu',
            '/media/mod_quickicon',
            '/media/mod_sampledata',
            '/media/plg_behaviour_compat',
            '/media/plg_captcha_recaptcha',
            '/media/plg_captcha_recaptcha_invisible',
            '/media/plg_content_vote',
            '/media/plg_editors-xtd_image',
            '/media/plg_editors_codemirror',
            '/media/plg_editors_none',
            '/media/plg_editors_tinymce',
            '/media/plg_installer_folderinstaller',
            '/media/plg_installer_packageinstaller',
            '/media/plg_installer_urlinstaller',
            '/media/plg_installer_webinstaller',
            '/media/plg_media-action_crop',
            '/media/plg_media-action_resize',
            '/media/plg_media-action_rotate',
            '/media/plg_multifactorauth_email',
            '/media/plg_multifactorauth_fixed',
            '/media/plg_multifactorauth_totp',
            '/media/plg_multifactorauth_webauthn',
            '/media/plg_multifactorauth_yubikey',
            '/media/plg_quickicon_eos',
            '/media/plg_quickicon_extensionupdate',
            '/media/plg_quickicon_joomlaupdate',
            '/media/plg_quickicon_overridecheck',
            '/media/plg_quickicon_privacycheck',
            '/media/plg_system_debug',
            '/media/plg_system_guidedtours',
            '/media/plg_system_jooa11y',
            '/media/plg_system_schedulerunner',
            '/media/plg_system_shortcut',
            '/media/plg_system_stats',
            '/media/plg_system_webauthn',
            '/media/plg_user_token',
            '/media/system',
            '/media/templates/administrator/atum',
            '/media/templates/site/cassiopeia',
            '/media/vendor/accessibility',
            '/media/vendor/awesomplete',
            '/media/vendor/bootstrap',
            '/media/vendor/choicesjs',
            '/media/vendor/chosen',
            '/media/vendor/codemirror',
            '/media/vendor/cropperjs',
            '/media/vendor/debugbar',
            '/media/vendor/diff',
            '/media/vendor/dragula',
            '/media/vendor/es-module-shims',
            '/media/vendor/focus-visible',
            '/media/vendor/fontawesome-free',
            '/media/vendor/hotkeysjs',
            '/media/vendor/joomla-custom-elements',
            '/media/vendor/jquery',
            '/media/vendor/jquery-migrate',
            '/media/vendor/mediaelement',
            '/media/vendor/metismenujs',
            '/media/vendor/minicolors',
            '/media/vendor/qrcode',
            '/media/vendor/roboto-fontface',
            '/media/vendor/sa11y',
            '/media/vendor/shepherdjs',
            '/media/vendor/short-and-sweet',
            '/media/vendor/skipto',
            '/media/vendor/tinymce',
            '/media/vendor/webcomponentsjs',
            '/modules/mod_articles',
            '/modules/mod_articles_archive',
            '/modules/mod_articles_categories',
            '/modules/mod_articles_category',
            '/modules/mod_articles_latest',
            '/modules/mod_articles_news',
            '/modules/mod_articles_popular',
            '/modules/mod_banners',
            '/modules/mod_breadcrumbs',
            '/modules/mod_custom',
            '/modules/mod_feed',
            '/modules/mod_finder',
            '/modules/mod_footer',
            '/modules/mod_languages',
            '/modules/mod_login',
            '/modules/mod_menu',
            '/modules/mod_random_image',
            '/modules/mod_related_items',
            '/modules/mod_stats',
            '/modules/mod_syndicate',
            '/modules/mod_tags_popular',
            '/modules/mod_tags_similar',
            '/modules/mod_users_latest',
            '/modules/mod_whosonline',
            '/modules/mod_wrapper',
            '/plugins/actionlog',
            '/plugins/api-authentication',
            '/plugins/authentication',
            '/plugins/behaviour',
            '/plugins/captcha',
            '/plugins/content',
            '/plugins/editors',
            '/plugins/editors-xtd',
            '/plugins/extension',
            '/plugins/fields',
            '/plugins/filesystem',
            '/plugins/finder',
            '/plugins/installer',
            '/plugins/media-action',
            '/plugins/multifactorauth',
            '/plugins/privacy',
            '/plugins/quickicon',
            '/plugins/sampledata',
            '/plugins/schemaorg',
            '/plugins/system',
            '/plugins/task',
            '/plugins/user',
            '/plugins/webservices',
            '/plugins/workflow',
            '/templates/cassiopeia',
            '/templates/system',
        ];

        foreach ($files as $file) {
            if (is_file(JPATH_ROOT . $file) && decoct(fileperms(JPATH_ROOT . $file) & 0777) === '777') {
                @chmod(JPATH_ROOT . $file, 0644);
            }
        }

        foreach ($folders as $folder) {
            if (is_dir(JPATH_ROOT . $folder)) {
                if (decoct(fileperms(JPATH_ROOT . $folder) & 0777) === '777') {
                    @chmod(JPATH_ROOT . $folder, 0755);
                }

                foreach (Folder::files(JPATH_ROOT . $folder, '.', false, true) as $file) {
                    if (decoct(fileperms($file) & 0777) === '777') {
                        @chmod($file, 0644);
                    }
                }
            }
        }

        foreach ($foldersRecursive as $parentFolder) {
            if (is_dir(JPATH_ROOT . $parentFolder)) {
                if (decoct(fileperms(JPATH_ROOT . $parentFolder) & 0777) === '777') {
                    @chmod(JPATH_ROOT . $parentFolder, 0755);
                }

                foreach (Folder::folders(JPATH_ROOT . $parentFolder, '.', true, true) as $folder) {
                    if (decoct(fileperms($folder) & 0777) === '777') {
                        @chmod($folder, 0755);
                    }
                }

                foreach (Folder::files(JPATH_ROOT . $parentFolder, '.', true, true) as $file) {
                    if (decoct(fileperms($file) & 0777) === '777') {
                        @chmod($file, 0644);
                    }
                }
            }
        }
    }
}
