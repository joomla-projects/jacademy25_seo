<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for loading application's config from environment variables.
 *
 * @since  __DEPLOY_VERSION__
 */
class EnvsConfig implements ServiceProviderInterface
{
    /**
     * Environment variables prefix.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const PREFIX = 'JOOMLA_';

    /**
     * Configuration loaded from environment variables.
     *
     * @var ?Registry
     */
    private static ?Registry $config = null;

    /**
     * Get a config value.
     *
     * @param  string  $key      Config key (e.g. 'debug', 'error_reporting').
     * @param  mixed   $default  Optional default value, returned if the internal value is null.
     *
     * @return  mixed  Value of entry or null
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function get(string $key, $default = null)
    {
        self::loadEnvs();

        return self::$config->get($key, $default);
    }

    /**
     * Check if specified config key exists.
     *
     * @param  string  $key  Config key (e.g. 'debug', 'error_reporting').
     *
     * @return  bool  true if key is defined, false otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function has(string $key)
    {
        self::loadEnvs();

        return self::$config->exists($key);
    }

    /**
     * Reload configuration from environment variables.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function reload()
    {
        self::$config = null;
    }

    /**
     * Load and process configuration from environment variables.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function loadEnvs()
    {
        if (self::$config !== null) {
            return;
        }

        // getenv() is not thread-safe and it can cause segmentaion fault, so we should try $_SERVER first
        $envs = empty($_SERVER) ? getenv() : $_SERVER;

        $config = new Registry();

        /**
         * Some envs names look strange and inconsistent e.g. JOOMLA_LIFETIME, JOOMLA_USER and etc.
         * We add alises for such variables: JOOMLA_SESSION_LIFETIME, JOOMLA_DBUSER and so on.
         */
        $aliases = [
            'dbhost'           => 'host',
            'dbname'           => 'db',
            'dbpassword'       => 'password',
            'dbuser'           => 'user',
            'meta_author'      => 'MetaAuthor',
            'meta_desc'        => 'MetaDesc',
            'meta_rights'      => 'MetaRights',
            'meta_version'     => 'MetaVersion',
            'locale_offset'    => 'offset',
            'session_lifetime' => 'lifetime',
        ];

        foreach ($envs as $envKey => $envValue) {
            if (!str_starts_with($envKey, self::PREFIX)) {
                continue;
            }

            $configKey = strtolower(substr($envKey, \strlen(self::PREFIX)));
            if (isset($aliases[$configKey])) {
                $configKey = $aliases[$configKey];
            }

            $configValue = $envValue;

            // Convert value to an appropriate type
            switch ($configKey) {
                case 'MetaAuthor':
                case 'MetaVersion':
                case 'behind_loadbalancer':
                case 'cache_platformprefix':
                case 'cors':
                case 'dbsslverifyservercert':
                case 'debug':
                case 'debug_lang':
                case 'debug_lang_const':
                case 'gzip':
                case 'mailonline':
                case 'massmailoff':
                case 'memcached_compress':
                case 'memcached_persist':
                case 'offline':
                case 'proxy_enable':
                case 'redis_persist':
                case 'sef':
                case 'sef_rewrite':
                case 'sef_suffix':
                case 'session_metadata':
                case 'shared_session':
                case 'smtpauth':
                case 'unicodeslugs':
                    $configValue = (bool) $configValue;
                    break;
                case 'access':
                case 'asset_id':
                case 'cachetime':
                case 'caching':
                case 'captcha':
                case 'dbencryption':
                case 'display_offline_message':
                case 'feed_limit':
                case 'force_ssl':
                case 'frontediting':
                case 'lifetime':
                case 'list_limit':
                case 'log_category_mode':
                case 'log_deprecated':
                case 'log_everything':
                case 'memcached_server_port':
                case 'redis_server_db':
                case 'redis_server_port':
                case 'session_memcached_server_port':
                case 'session_redis_persist':
                case 'session_redis_server_db':
                case 'session_redis_server_port':
                case 'sitename_pagetitles':
                case 'smtpport':
                    $configValue = (int) $configValue;
                    break;
                case 'log_priorities':
                    $configValue = json_decode($configValue, true);
            }

            $config->set($configKey, $configValue);
        }

        self::$config = $config;
    }

    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function register(Container $container)
    {
        $container->share(
            'envs_config',
            function () {
                self::loadEnvs();

                return self::$config;
            },
            true
        );
    }
}
