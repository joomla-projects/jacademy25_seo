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


        $componentName = '';
        if ($this->form) {
            $componentName = (string) ($this->form->getValue('extension')
                ?: $this->form->getData()->get('extension'));
        }
        if (!$componentName) {
            $context       = (string) ($this->form ? $this->form->getName() : '');
            $componentName = $context ? explode('.', $context, 2)[0] ?? '' : '';
            if (!$componentName) {
                $componentName = (string) $app->input->getCmd('option', '');
            }
        }
        if (!$componentName) {
            return $groups;
        }

        try {
            $component = $app->bootComponent($componentName);
        } catch (\Throwable $e) {
            return $groups;
        }

        if (!$component instanceof OpengraphServiceInterface) {
            return $groups;
        }


        $ogOptions = [];

        $fields    = $component->getOpengraphFields();
        $fieldType = $this->getAttribute('field-type');

        if (isset($fields[$fieldType])) {
            foreach ($fields[$fieldType] as $value => $text) {
                $ogOptions[] = HTMLHelper::_('select.option', $value, $text);
            }
        }

        if (!empty($ogOptions)) {
            $groups[Text::_('PLG_SYSTEM_OPENGRAPH_GROUP_DEFAULT_FIELDS')] = $ogOptions;
        }


        return $groups;
    }
}
