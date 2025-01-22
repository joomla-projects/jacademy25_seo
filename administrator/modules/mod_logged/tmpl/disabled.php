<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div class="mb-4">
    <p class="fw-bold text-center text-muted">
        <!-- <span class="icon-users" aria-hidden="true"></span> -->
        <svg class="j-icon" aria-hidden="true"><use href="#<?= HTMLHelper::_('svgicon.add', 'j--users'); ?>"></svg>
        <?php echo Text::_('MOD_LOGGED_NO_SESSION_METADATA'); ?>
    </p>
</div>
