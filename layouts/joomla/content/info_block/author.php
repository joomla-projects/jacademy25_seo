<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<dd class="createdby">
    <span class="icon-user icon-fw" aria-hidden="true"></span>
    <?php $author = ($displayData['item']->created_by_alias ?: $displayData['item']->author); ?>
    <?php $author = '<span>' . $author . '</span>'; ?>
    <?php if (!empty($displayData['item']->contact_link) && $displayData['params']->get('link_author')) : ?>
        <?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', HTMLHelper::_('link', $displayData['item']->contact_link, $author)); ?>
    <?php else : ?>
        <?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
    <?php endif; ?>
</dd>
