<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Service\Provider\EnvsConfig;

// Check if a configuration file already exists.
if (
    file_exists(JPATH_CONFIGURATION . '/configuration.php')
    && (filesize(JPATH_CONFIGURATION . '/configuration.php') > 10)
    && !file_exists(JPATH_INSTALLATION . '/index.php')
) {
    header('Location: ../index.php');
    exit();
}

// Import the Joomla Platform.
require_once JPATH_LIBRARIES . '/bootstrap.php';

// Ensure sensible default for JDEBUG is set.
\define('JDEBUG', EnvsConfig::get('debug', false));

// If debug mode enabled, set new Exception handler with debug enabled.
if (JDEBUG) {
    $errorHandler->setExceptionHandler(
        [
            new \Symfony\Component\ErrorHandler\ErrorHandler(null, true),
            'renderException',
        ]
    );
}
