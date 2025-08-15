<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Opengraph\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Opengraph\MappableFieldInterface;
use Joomla\CMS\Opengraph\OpengraphGroup;
use Joomla\CMS\Opengraph\OpengraphServiceInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

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


        $component = '';
        if ($this->form) {
            $component = (string) ($this->form->getValue('extension')
                ?: $this->form->getData()->get('extension'));
        }
        if (!$component) {
            $context = (string) ($this->form ? $this->form->getName() : '');
            $component = $context ? explode('.', $context, 2)[0] ?? '' : '';
            if (!$component) {
                $component = (string) $app->input->getCmd('option', '');
            }
        }
        if (!$component) {
            return $groups;
        }

        try {
            $cmp = $app->bootComponent($component);
        } catch (\Throwable $e) {
            return $groups;
        }

        if (!$cmp instanceof OpengraphServiceInterface) {
            return $groups;
        }


        $ogOptions = [];

        $fields    = $cmp->getOpengraphFields();
        $fieldType = $this->getAttribute('field-type');

        if (isset($fields[$fieldType])) {
            foreach ($fields[$fieldType] as $value => $text) {
                $ogOptions[] = HTMLHelper::_('select.option', $value, $text);
            }
        }

        if (!empty($ogOptions)) {
            $groups['Default Fields'] = $ogOptions;
        }


        if (!$component instanceof FieldsServiceInterface) {
            return $groups;
        }

        // Allowed field types for each OpenGraph group
        $allowedFieldTypes = [
            OpengraphGroup::TEXT->value       => ['text', 'textarea'],
            OpengraphGroup::IMAGE->value      => ['media', 'imagelist'],
            OpengraphGroup::IMAGE_ALT->value  => ['text'],
        ];

        $nativeTypes = $allowedFieldTypes[$fieldType] ?? [];



        $catId = (int) $this->form->getValue('id');        // editing existing cat
        if (!$catId) {
            // Creating a new category: use the chosen parent so assignments still work
            $catId = (int) $this->form->getValue('parent_id');
        }

        // Dummy item with catid so FieldsService filters by assignment
        $scopeItem = $catId ? (object) ['catid' => $catId] : null;

        $customFields  = FieldsHelper::getFields('com_content.article', $scopeItem);
        $customOptions = [];

        foreach ($customFields as $field) {
            $accept = \in_array($field->type, $nativeTypes, true);


            // If not native-allowed, see if the field’s plugin implements our interface
            if (!$accept) {

                // Ensure the specific fields plugin is loaded
                PluginHelper::importPlugin('fields', $field->type);

                $ucType = ucfirst((string) $field->type);

                // Candidate class names in priority order (modern first, then legacy)
                $candidates = [
                    "Joomla\\Plugin\\Fields\\{$ucType}\\Extension\\{$ucType}", // J4/5 namespaced
                    "Joomla\\Plugin\\Fields\\{$ucType}\\Field\\{$ucType}Field", // some third-party patterns
                    "PlgFields{$ucType}",                                      // legacy non-namespaced
                ];

                $implements = false;

                foreach ($candidates as $fqcn) {
                    if (\class_exists($fqcn) && \is_subclass_of($fqcn, MappableFieldInterface::class)) {
                        $implements = ($fqcn::getOpengraphGroup()->value === $fieldType);
                        if ($implements) {
                            $accept = true;
                            break;
                        }
                    }
                }
            }


            if (!$accept) {
                continue;
            }



            $label           = $field->title . ' (' . $field->name . ')';
            $customOptions[] = HTMLHelper::_('select.option', 'field.' . $field->name, $label);
        }

        if (!empty($customOptions)) {
            $groups['Custom Fields'] = $customOptions;
        }

        return $groups;
    }
}
