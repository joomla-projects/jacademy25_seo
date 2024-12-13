<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Plugin\System\Schemaorg;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for SchemaBeforeCompileHeadEvent event
 * Example:
 *  new BeforeCompileHeadEvent('onSchemaBeforeCompileHead', ['subject' => $schema, 'context' => 'com_example.example']);
 *
 * @since  5.0.0
 */
class BeforeCompileHeadEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('subject', $arguments)) {
            throw new \BadMethodCallException(sprintf("Argument 'subject' of event %s is required but has not been provided", $name));
        }

        if (!\array_key_exists('context', $arguments)) {
            throw new \BadMethodCallException(sprintf("Argument 'context' of event %s is required but has not been provided", $name));
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument.
     *
     * @param   Registry  $value  The value to set
     *
     * @return  Registry
     *
     * @since   5.0.0
     */
    protected function onSetSubject(Registry $value): Registry
    {
        return $value;
    }

    /**
     * Setter for the context argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since   5.0.0
     */
    protected function onSetContext(string $value): string
    {
        return $value;
    }

    /**
     * Getter for the schema argument.
     *
     * @return  Registry
     *
     * @since   5.0.0
     */
    public function getSchema(): Registry
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the context argument.
     *
     * @return  string
     *
     * @since   5.0.0
     */
    public function getContext(): string
    {
        return $this->arguments['context'];
    }
}
