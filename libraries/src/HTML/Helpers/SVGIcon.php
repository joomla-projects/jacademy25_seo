<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for inline SVG icons
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class SVGIcon
{
    /**
     * Has the class been initialised
     *
     * @var    bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected static $initialized = false;

    /**
     * Array containing the selected icons, as name => symbol.
     *
     * @var    array
     *
     * @since  __DEPLOY_VERSION__
     */
    protected static $selected = [];

    /**
     * Array containing the pool of icons, as name => symbol.
     *
     * @var    array
     *
     * @since  __DEPLOY_VERSION__
     */
    protected static $pool = [];

    /**
     * Imports a single icon to the pool
     *
     * @param  string  $name  the name of the icon
     * @param  string  $code  the svg code of the icon
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected static function importIcon(string $name, string $code)
    {
        static::$pool[$name] = $code;
    }

    /**
     * Imports an array of icons to the pool
     *
     * @param  array  $icons
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function importIcons(array $icons): void
    {
        if (static::$initialized !== true && is_file(JPATH_LIBRARIES . '/svg_icons.php')) {
            $init = require JPATH_LIBRARIES . '/svg_icons.php';

            foreach ($init as $name => $code) {
                static::importIcon($name, $code);
            }

            static::$initialized = true;
        }

        foreach($icons as $name => $code) {
            static::importIcon($name, $code);
        }
    }

    /**
     * Registers an icon
     *
     * @param  string  $name  the name of the icon
     *
     * @return  string  the name of the icon
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function add(string $name): string
    {
        if (!static::$initialized) {
            static::importIcons([]);

            static::$initialized = true;
        }

        if (!isset(static::$pool[$name])) {
            return '';
        }

        if (isset(static::$pool[$name]) && !isset(static::$selected[$name])) {
            static::$selected[$name] = static::$pool[$name];
        }

        return $name;
    }

    /**
     * Returns a specific selected icon
     *
     * @param   string $name  the name of the icon
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function get(string $name): string
    {
        return !isset(static::$selected[$name]) ? '' : static::$selected[$name];
    }

    /**
     * Returns all the currently selected icons
     *
     * @return  array  the array of the selected icons
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getAll(): array
    {
        return static::$selected;
    }
}
