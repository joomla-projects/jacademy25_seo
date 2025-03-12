<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\Event\SubscriberInterface;

/**
 * An interface to help transitioning to  SubscriberInterface for plugins that implement Lazy Object.
 * For plugins which implement this interface method registerListeners() will not be called.
 *
 * @TODO Remove in 8.0
 *
 * @since  __DEPLOY_VERSION__
 */
interface LazySubscriberInterface extends SubscriberInterface
{
}
