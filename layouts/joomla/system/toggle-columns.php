<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

$attributes = $displayData;

/**
 * Layout variables
 * -----------------
 * @var   array  $attributes  The level of the item in the tree like structure.
 *
 * @since  __DEPLOY_VERSION__
 */

 // joomla-toggle-columns web component
Factory::getApplication()->getDocument()->getWebAssetManager()
    ->useScript('joomla.columns.toggle');

?>

<?php if (!empty($attributes)) : ?>
    <joomla-columns-toggle
        <?php foreach ($attributes as $key => $value) : ?>
            <?php echo $key . '="' . $value . '" '; ?>
        <?php endforeach; ?>>

        <div class="dropdown float-end pb-2">
            <button type="button"
                class="btn btn-primary btn-sm dropdown-toggle"
                data-bs-toggle="dropdown" data-bs-auto-close="false"
                aria-haspopup="true" aria-expanded="false">
                0/0 Columns
            </button>
            <div class="dropdown-menu dropdown-menu-end" data-bs-popper="static">
                <ul class="list-unstyled p-2 text-nowrap mb-0" id="columnList">
                </ul>
            </div>
        </div>

    </joomla-columns-toggle>
<?php endif; ?>
