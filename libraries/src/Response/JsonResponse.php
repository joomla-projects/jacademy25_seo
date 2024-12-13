<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Response;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JSON Response class.
 *
 * This class serves to provide the Joomla Platform with a common interface to access
 * response variables for e.g. Ajax requests.
 *
 * @since  3.1
 */
class JsonResponse implements \Stringable
{
    /**
     * Determines whether the request was successful
     *
     * @var    boolean
     *
     * @since  3.1
     */
    public $success = true;

    /**
     * Array of messages gathered in the Application object
     *
     * @var    array
     *
     * @since  3.1
     */
    public $messages;

    /**
     * The response data
     *
     * @var    mixed
     *
     * @since  3.1
     */
    public $data;

    /**
     * Constructor
     *
     * @param   mixed    $response        The Response data
     * @param   string   $message         The main response message
     * @param   boolean  $error           True, if the success flag shall be set to false, defaults to false
     * @param   boolean  $ignoreMessages  True, if the message queue shouldn't be included, defaults to false
     *
     * @since   3.1
     */
    public function __construct($response = null, /**
     * The main response message
     *
     *
     * @since  3.1
     */
    public $message = null, $error = false, $ignoreMessages = false)
    {
        // Get the message queue if requested and available
        $app = Factory::getApplication();

        if (!$ignoreMessages && $app !== null && \is_callable($app->getMessageQueue(...))) {
            $messages = $app->getMessageQueue();
            $lists    = [];

            // Build the sorted messages list
            if (\is_array($messages) && \count($messages)) {
                foreach ($messages as $this->message) {
                    if (isset($this->message['type']) && isset($this->message['message'])) {
                        $lists[$this->message['type']][] = $this->message['message'];
                    }
                }
            }

            // If messages exist add them to the output
            if ($lists !== []) {
                $this->messages = $lists;
            }
        }

        // Check if we are dealing with an error
        if ($response instanceof \Throwable) {
            // Prepare the error response
            $this->success = false;
            $this->message = $response->getMessage();
        } else {
            // Prepare the response data
            $this->success = !$error;
            $this->data    = $response;
        }
    }

    /**
     * Magic toString method for sending the response in JSON format
     *
     * @return  string  The response in JSON format
     *
     * @since   3.1
     */
    public function __toString(): string
    {
        return (string) json_encode($this);
    }
}
