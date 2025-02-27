<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

if (empty($field->value) || empty($field->value['imagefile'])) {
    return;
}

$fileUrl = MediaHelper::getCleanMediaFieldValue($field->value['imagefile']);

$class = $fieldParams->get('image_class');
$options = [];

if ($class) {
    $options['class'] = $class;
}

if (MediaHelper::isImage($fileUrl) || MediaHelper::getMimeType($fileUrl) === 'image/svg+xml') {
    $options = [
        'src' => $field->value['imagefile'],
        'alt' => empty($field->value['alt_text']) && empty($field->value['alt_empty']) ? false : $field->value['alt_text'],
    ];

    if ($class) {
        $options['class'] = $class;
    }

    echo LayoutHelper::render('joomla.html.image', $options);
} else {
    $linkText = $field->value['linktext'] ?? Text::_('JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_LINKTEXT_DEFAULT_VALUE');

    echo HTMLHelper::link($fileUrl, $linkText, $options);
}
