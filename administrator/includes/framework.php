<?php

/**
 * @package    Joomla.Administrator
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Version;
use Joomla\Utilities\IpHelper;
use Symfony\Component\Dotenv\Dotenv;

// System includes
require_once JPATH_LIBRARIES . '/bootstrap.php';

// Installation check, and check on removal of the install directory
if (
    !file_exists(JPATH_CONFIGURATION . '/configuration.php')
    || (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10)
    || (file_exists(JPATH_INSTALLATION . '/index.php') && (false === (new Version())->isInDevelopmentState()))
) {
    if (!file_exists(JPATH_INSTALLATION . '/index.php')) {
        echo 'No configuration file found and no installation code available. Exiting...';

        exit;
    }

    if (JPATH_ROOT === JPATH_PUBLIC) {
        header('Location: ../installation/index.php');

        exit;
    }

    echo 'Installation from a public folder is not supported, revert your Server configuration to point at Joomla\'s root folder to continue.';

    exit;
}

// Load .env files
if (file_exists(JPATH_ROOT . '/.env.local.php') || file_exists(JPATH_ROOT . '/.env')) {
    (new Dotenv('JOOMLA_ENV', 'JOOMLA_DEBUG'))->bootEnv(JPATH_ROOT . '/.env', 'prod');
}

// Pre-Load configuration. Don't remove the Output Buffering due to BOM issues, see JCode 26026
ob_start();
require_once JPATH_CONFIGURATION . '/configuration.php';
ob_end_clean();

// getenv() is not thread-safe and it can cause segmentaion fault, so we should try $_SERVER first
$envs = !empty($_SERVER) ? $_SERVER : getenv();

// System configuration.
$config                  = new JConfig();
$config->error_reporting = $envs['JOOMLA_ERROR_REPORTING'] ?? $config->error_reporting;
$config->debug           = (bool) ($envs['JOOMLA_DEBUG'] ?? $config->debug);
if (isset($envs['JOOMLA_LOG_DEPRECATED'])) {
    $config->log_deprecated = (int) $envs['JOOMLA_LOG_DEPRECATED'];
}
if (isset($envs['JOOMLA_BEHIND_LOADBALANCER'])) {
    $config->behind_loadbalancer = (bool) $envs['JOOMLA_BEHIND_LOADBALANCER'];
}
unset($envs);

// Set the error_reporting, and adjust a global Error Handler
switch ($config->error_reporting) {
    case 'default':
    case '-1':
        break;

    case 'none':
    case '0':
        error_reporting(0);

        break;

    case 'simple':
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        ini_set('display_errors', 1);

        break;

    case 'maximum':
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        break;

    default:
        error_reporting($config->error_reporting);
        ini_set('display_errors', 1);

        break;
}

\define('JDEBUG', $config->debug);

// Check deprecation logging
if (empty($config->log_deprecated)) {
    // Reset handler for E_USER_DEPRECATED
    set_error_handler(null, E_USER_DEPRECATED);
} else {
    // Make sure handler for E_USER_DEPRECATED is registered
    set_error_handler(['Joomla\CMS\Exception\ExceptionHandler', 'handleUserDeprecatedErrors'], E_USER_DEPRECATED);
}

if (JDEBUG || $config->error_reporting === 'maximum') {
    // Set new Exception handler with debug enabled
    $errorHandler->setExceptionHandler(
        [
            new \Symfony\Component\ErrorHandler\ErrorHandler(null, true),
            'renderException',
        ]
    );
}

/**
 * Correctly set the allowing of IP Overrides if behind a trusted proxy/load balancer.
 *
 * We need to do this as high up the stack as we can, as the default in \Joomla\Utilities\IpHelper is to
 * $allowIpOverride = true which is the wrong default for a generic site NOT behind a trusted proxy/load balancer.
 */
if (property_exists($config, 'behind_loadbalancer') && $config->behind_loadbalancer == 1) {
    // If Joomla is configured to be behind a trusted proxy/load balancer, allow HTTP Headers to override the REMOTE_ADDR
    IpHelper::setAllowIpOverrides(true);
} else {
    // We disable the allowing of IP overriding using headers by default.
    IpHelper::setAllowIpOverrides(false);
}

unset($config);
