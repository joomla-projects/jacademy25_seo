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
 * Enumerates the logical OpenGraph groups that a custom field-type can
 * register itself under.
 *
 * Third-party field-type plugins should return one of these cases from
 * {@see MappableFieldInterface::getOpengraphGroup()} so the System – OpenGraph
 * plugin knows how to categorise the field inside its mapping drop-down.
 *
 * @since  __DEPLOY_VERSION__
 */
enum OpengraphGroup: string
{
/** Standard textual content (e.g. single-line, multi-line). */
    case TEXT       = 'text-fields';

/** Image or media file (intro/full images, custom media fields, …). */
    case IMAGE      = 'image-fields';

/** Alternate-text associated with an image (accessibility / SEO). */
    case IMAGE_ALT  = 'image-alt-fields';

    // TODO : implement these cases in the future, if needed

    // /** Meta-information such as description or keywords. */
    // case META       = 'meta-fields';

    // /** Locale or language codes (e.g. `en-US`). */
    // case LOCALE     = 'locale-fields';

    // /** Author-related data (creator, modifier, alias, …). */
    // case AUTHOR     = 'author-fields';

    // /** Date-time values (created, modified, publish-up/down, …). */
    // case DATE       = 'date-fields';
}

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
