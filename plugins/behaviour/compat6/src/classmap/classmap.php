<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
\defined('_JEXEC') or die;

require_once __DIR__ . '/extensions.classmap.php';

// As JLoader is not managing the \Joomla\Input namespace, we need to use the native class alias function
class_alias('\\Joomla\\Input\\Input', '\\Joomla\\CMS\\Input\\Input');
