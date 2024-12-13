<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Service\HTML;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy component HTML helper.
 *
 * @since  3.9.0
 */
class Privacy
{
    /**
     * Render a status badge
     *
     * @param   integer  $status  The item status
     *
     * @return  string
     *
     * @since   3.9.0
     */
    public function statusLabel($status)
    {
        return match ($status) {
            2 => '<span class="badge bg-success">' . Text::_('COM_PRIVACY_STATUS_COMPLETED') . '</span>',
            1 => '<span class="badge bg-info">' . Text::_('COM_PRIVACY_STATUS_CONFIRMED') . '</span>',
            -1 => '<span class="badge bg-danger">' . Text::_('COM_PRIVACY_STATUS_INVALID') . '</span>',
            0 => '<span class="badge bg-warning">' . Text::_('COM_PRIVACY_STATUS_PENDING') . '</span>',
            default => null,
        };
    }
}
