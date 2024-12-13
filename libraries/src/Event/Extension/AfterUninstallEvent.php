<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Extension;

use Joomla\CMS\Installer\Installer;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Extension events
 *
 * @since  5.0.0
 */
class AfterUninstallEvent extends AbstractExtensionEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['installer', 'eid', 'removed'];

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
        parent::__construct($name, $arguments);

        if (!\array_key_exists('installer', $this->arguments)) {
            throw new \BadMethodCallException(\sprintf("Argument 'method' of event %s is required but has not been provided", $name));
        }

        if (!\array_key_exists('eid', $this->arguments)) {
            throw new \BadMethodCallException(\sprintf("Argument 'eid' of event %s is required but has not been provided", $name));
        }

        if (!\array_key_exists('removed', $this->arguments)) {
            throw new \BadMethodCallException(\sprintf("Argument 'removed' of event %s is required but has not been provided", $name));
        }
    }

    /**
     * Setter for the installer argument.
     *
     * @param   Installer  $value  The value to set
     *
     * @return  Installer
     *
     * @since  5.0.0
     */
    protected function onSetInstaller(Installer $value): Installer
    {
        return $value;
    }

    /**
     * Setter for the eid argument.
     *
     * @param   integer  $value  The value to set
     *
     * @return  integer
     *
     * @since  5.0.0
     */
    protected function onSetEid(int $value): int
    {
        return $value;
    }

    /**
     * Setter for the removed argument.
     *
     * @param   bool  $value  The value to set
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    protected function onSetRemoved(bool $value): bool
    {
        return $value;
    }

    /**
     * Getter for the installer.
     *
     * @return  Installer
     *
     * @since  5.0.0
     */
    public function getInstaller(): Installer
    {
        return $this->arguments['installer'];
    }

    /**
     * Getter for the eid.
     *
     * @return  integer
     *
     * @since  5.0.0
     */
    public function getEid(): int
    {
        return $this->arguments['eid'];
    }

    /**
     * Getter for the removed.
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    public function getRemoved(): bool
    {
        return $this->arguments['removed'];
    }
}
