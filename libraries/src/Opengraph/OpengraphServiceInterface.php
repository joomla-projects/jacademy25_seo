<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Opengraph;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * The Opengraph service.
 *
 * @since  __DEPLOY_VERSION__
 */
interface OpengraphServiceInterface
{
    /**
     * Returns valid contexts.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     *
     */
    public function getOpengraphFields(): array;
}




interface MappableFieldInterface
{
    /**
     * Returns the OpenGraph group this field should be listed under.
     *
     * @return  OpengraphGroup  One of the enum cases defined in {@see OpengraphGroup}.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getOpengraphGroup(): OpengraphGroup;
}
