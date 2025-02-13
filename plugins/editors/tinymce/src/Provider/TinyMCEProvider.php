<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\Provider;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Editor\AbstractEditorProvider;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Editors\TinyMCE\PluginTraits\DisplayTrait;
use Joomla\Registry\Registry;

/**
 * Editor provider class
 *
 * @since   5.0.0
 */
final class TinyMCEProvider extends AbstractEditorProvider
{
    use DisplayTrait;
    use DatabaseAwareTrait;

    /**
     * Class constructor
     *
     * @param   Registry                 $params
     * @param   CMSApplicationInterface  $application
     * @param   DispatcherInterface      $dispatcher
     * @param   DatabaseInterface        $database
     *
     * @since  5.0.0
     */
    public function __construct(
        /**
         * A Registry object holding the parameters for the plugin
         *
         * @since  5.0.0
         */
        protected Registry $params,
        /**
         * The application object
         *
         *
         * @since  5.0.0
         */
        protected CMSApplicationInterface $application,
        DispatcherInterface $dispatcher,
        DatabaseInterface $database
    ) {
        $this->setDispatcher($dispatcher);
        $this->setDatabase($database);
    }

    /**
     * Return Editor name, CMD string.
     *
     * @return string
     * @since   5.0.0
     */
    public function getName(): string
    {
        return 'tinymce';
    }
}
