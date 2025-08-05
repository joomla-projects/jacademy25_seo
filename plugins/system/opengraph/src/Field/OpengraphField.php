<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Opengraph\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Opengraph\OpengraphServiceInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @since  __DEPLOY_VERSION__
 */

class OpengraphField extends GroupedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'Opengraph';


    /**
     * Method to get the field options.
     *
     * @return  object[]  The field option objects.
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function getGroups()
    {
        $app    = Factory::getApplication();
        $groups = [];

        $groups[''] = [
            HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_OPENGRAPH_NO_FIELD_SELECTED')),
        ];


        $ogOptions = [];
        $component = $app->bootComponent('com_content');

        if (!$component instanceof OpengraphServiceInterface) {
            return $groups;
        }

        $fields    = $component->getOpengraphFields();
        $fieldType = $this->getAttribute('field-type');

        if (isset($fields[$fieldType])) {
            foreach ($fields[$fieldType] as $value => $text) {
                $ogOptions[] = HTMLHelper::_('select.option', $value, $text);
            }
        }

        if (!empty($ogOptions)) {
            $groups['Default Fields'] = $ogOptions;
        }


        return $groups;
    }
}
