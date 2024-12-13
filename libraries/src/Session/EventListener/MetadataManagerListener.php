<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session\EventListener;

use Joomla\CMS\Session\MetadataManager;
use Joomla\Registry\Registry;
use Joomla\Session\SessionEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event listener for session events regarding the session metadata for users.
 *
 * @since  4.0.0
 */
final class MetadataManagerListener
{
    /**
     * Constructor.
     *
     * @param   MetadataManager  $metadataManager  Session metadata manager.
     * @param   Registry         $config           Application configuration.
     *
     * @since   4.0.0
     */
    public function __construct(
        /**
         * Session metadata manager.
         *
         * @since  4.0.0
         */
        private readonly MetadataManager $metadataManager,
        /**
         * Application configuration.
         *
         * @since  4.0.0
         */
        private readonly Registry $config
    )
    {
    }

    /**
     * Listener for the `session.start` event.
     *
     * @param   SessionEvent  $event  The session event.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAfterSessionStart(SessionEvent $event)
    {
        // Whether to track Session Metadata
        if (!$this->config->get('session_metadata', true) || !$event->getSession()->has('user')) {
            return;
        }

        $user = $event->getSession()->get('user');

        // Whether to track Session Metadata for Guest user
        if (!$this->config->get('session_metadata_for_guest', true) && !$user->id) {
            return;
        }

        $this->metadataManager->createOrUpdateRecord($event->getSession(), $user);
    }
}
