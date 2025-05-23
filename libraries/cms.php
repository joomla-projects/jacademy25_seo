<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

trigger_error(
    sprintf(
        'Bootstrapping Joomla using the %1$s file is deprecated.  Use %2$s instead.',
        __FILE__,
        __DIR__ . '/bootstrap.php'
    ),
    E_USER_DEPRECATED
);

// Import the library loader if necessary
if (!class_exists('JLoader')) {
    require_once JPATH_LIBRARIES . '/loader.php';
}

// Make sure that the Joomla Platform has been successfully loaded
if (!class_exists('JLoader')) {
    throw new RuntimeException('Joomla Platform not loaded.');
}

// Create the Composer autoloader
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';

// We need to pull our decorated class loader into memory before unregistering Composer's loader
class_exists('\\Joomla\\CMS\\Autoload\\ClassLoader');

$loader->unregister();

// Decorate Composer autoloader
spl_autoload_register([new \Joomla\CMS\Autoload\ClassLoader($loader), 'loadClass'], true, true);

// Define the Joomla version if not already defined
if (!defined('JVERSION')) {
    define('JVERSION', (new \Joomla\CMS\Version())->getShortVersion());
}

// Register a handler for uncaught exceptions that shows a pretty error page when possible
set_exception_handler(['Joomla\CMS\Exception\ExceptionHandler', 'handleException']);

// Set up the message queue logger for web requests
if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
    \Joomla\CMS\Log\Log::addLogger(['logger' => 'messagequeue'], \Joomla\CMS\Log\Log::ALL, ['jerror']);
}

// Register the Crypto lib
JLoader::register('Crypto', JPATH_LIBRARIES . '/php-encryption/Crypto.php');
