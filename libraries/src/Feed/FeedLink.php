<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Feed Link class.
 *
 * @since  3.1.4
 */
class FeedLink
{
    /**
     * The length of the resource in bytes.
     *
     * @var    integer
     * @since  3.1.4
     */
    public $length;

    /**
     * The link of the image.
     *
     * @var    integer
     * @since  4.4.0
     */
    public $link;

    /**
     * The description of the image.
     *
     * @var    integer
     * @since  4.4.0
     */
    public $description;

    /**
     * The height of the image.
     *
     * @var    integer
     * @since  4.4.0
     */
    public $height;

    /**
     * The width of the image.
     *
     * @var    integer
     * @since  4.4.0
     */
    public $width;

    /**
     * Constructor.
     *
     * @param   string   $uri       The URI to the linked resource.
     * @param   string   $relation  The relationship between the feed and the linked resource.
     * @param   string   $type      The resource type.
     * @param   string   $language  The language of the resource found at the given URI.
     * @param   string   $title     The title of the resource.
     * @param   integer  $length    The length of the resource in bytes.
     *
     * @since   3.1.4
     * @throws  \InvalidArgumentException
     */
    public function __construct(/**
     * The URI to the linked resource.
     *
     * @since  3.1.4
     */
    public $uri = null, /**
     * The relationship between the feed and the linked resource.
     *
     * @since  3.1.4
     */
    public $relation = null, /**
     * The resource type.
     *
     * @since  3.1.4
     */
    public $type = null, /**
     * The language of the resource found at the given URI.
     *
     * @since  3.1.4
     */
    public $language = null, /**
     * The title of the resource.
     *
     * @since  3.1.4
     */
    public $title = null, $length = null)
    {
        // Validate the length input.
        if (isset($length) && !is_numeric($length)) {
            throw new \InvalidArgumentException('Length must be numeric.');
        }

        $this->length = (int) $length;
    }
}
