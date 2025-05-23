<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\ContentHistory;
use Joomla\CMS\Table\ContentType;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Categories helper.
 *
 * @since  3.2
 */
class ContenthistoryHelper
{
    /**
     * Method to put all field names, including nested ones, in a single array for easy lookup.
     *
     * @param   \stdClass  $object  Standard class object that may contain one level of nested objects.
     *
     * @return  array  Associative array of all field names, including ones in a nested object.
     *
     * @since   3.2
     */
    public static function createObjectArray($object)
    {
        $result = [];

        if ($object === null) {
            return $result;
        }

        foreach ($object as $name => $value) {
            $result[$name] = $value;

            if (\is_object($value)) {
                foreach ($value as $subName => $subValue) {
                    $result[$subName] = $subValue;
                }
            }
        }

        return $result;
    }

    /**
     * Method to decode JSON-encoded fields in a standard object. Used to unpack JSON strings in the content history data column.
     *
     * @param   string  $jsonString  JSON String to convert to an object.
     *
     * @return  \stdClass  Object with any JSON-encoded fields unpacked.
     *
     * @since   3.2
     */
    public static function decodeFields($jsonString)
    {
        $object = json_decode($jsonString);

        if (\is_object($object)) {
            foreach ($object as $name => $value) {
                if (!\is_null($value) && $subObject = json_decode($value)) {
                    $object->$name = $subObject;
                }
            }
        }

        return $object;
    }

    /**
     * Method to get field labels for the fields in the JSON-encoded object.
     * First we see if we can find translatable labels for the fields in the object.
     * We translate any we can find and return an array in the format object->name => label.
     *
     * @param   \stdClass    $object      Standard class object in the format name->value.
     * @param   ContentType  $typesTable  Table object with content history options.
     *
     * @return  \stdClass  Contains two associative arrays.
     *                    $formValues->labels in the format name => label (for example, 'id' => 'Article ID').
     *                    $formValues->values in the format name => value (for example, 'state' => 'Published'.
     *                    This translates the text from the selected option in the form.
     *
     * @since   3.2
     */
    public static function getFormValues($object, ContentType $typesTable)
    {
        $labels              = [];
        $values              = [];
        $expandedObjectArray = static::createObjectArray($object);
        static::loadLanguageFiles($typesTable->type_alias);

        if ($formFile = static::getFormFile($typesTable)) {
            if ($xml = simplexml_load_file($formFile)) {
                // Now we need to get all of the labels from the form
                $fieldArray = $xml->xpath('//field');
                $fieldArray = array_merge($fieldArray, $xml->xpath('//fields'));

                foreach ($fieldArray as $field) {
                    if ($label = (string) $field->attributes()->label) {
                        $labels[(string) $field->attributes()->name] = Text::_($label);
                    }
                }

                // Get values for any list type fields
                $listFieldArray = $xml->xpath('//field[@type="list" or @type="radio"]');

                foreach ($listFieldArray as $field) {
                    $name = (string) $field->attributes()->name;

                    if (isset($expandedObjectArray[$name])) {
                        $optionFieldArray = $field->xpath('option[@value="' . $expandedObjectArray[$name] . '"]');

                        $valueText = null;

                        if (\is_array($optionFieldArray) && \count($optionFieldArray)) {
                            $valueText = trim((string) $optionFieldArray[0]);
                        }

                        $values[(string) $field->attributes()->name] = Text::_($valueText);
                    }
                }
            }
        }

        $result         = new \stdClass();
        $result->labels = $labels;
        $result->values = $values;

        return $result;
    }

    /**
     * Method to get the XML form file for this component. Used to get translated field names for history preview.
     *
     * @param   ContentType  $typesTable  Table object with content history options.
     *
     * @return  mixed  \JModel object if successful, false if no model found.
     *
     * @since   3.2
     */
    public static function getFormFile(ContentType $typesTable)
    {
        // First, see if we have a file name in the $typesTable
        $options = json_decode($typesTable->content_history_options);

        if (\is_object($options) && isset($options->formFile) && is_file(JPATH_ROOT . '/' . $options->formFile)) {
            $result = JPATH_ROOT . '/' . $options->formFile;
        } else {
            $aliasArray = explode('.', $typesTable->type_alias);
            $component  = ($aliasArray[1] == 'category') ? 'com_categories' : $aliasArray[0];
            $path       = Folder::makeSafe(JPATH_ADMINISTRATOR . '/components/' . $component . '/models/forms/');
            array_shift($aliasArray);
            $file   = File::makeSafe(implode('.', $aliasArray) . '.xml');
            $result = is_file($path . $file) ? $path . $file : false;
        }

        return $result;
    }

    /**
     * Method to query the database using values from lookup objects.
     *
     * @param   \stdClass  $lookup  The std object with the values needed to do the query.
     * @param   mixed      $value   The value used to find the matching title or name. Typically the id.
     *
     * @return  mixed  Value from database (for example, name or title) on success, false on failure.
     *
     * @since   3.2
     */
    public static function getLookupValue($lookup, $value)
    {
        $result = false;

        if (isset($lookup->sourceColumn, $lookup->targetTable, $lookup->targetColumn, $lookup->displayColumn)) {
            $db    = Factory::getDbo();
            $value = (int) $value;
            $query = $db->getQuery(true);
            $query->select($db->quoteName($lookup->displayColumn))
                ->from($db->quoteName($lookup->targetTable))
                ->where($db->quoteName($lookup->targetColumn) . ' = :value')
                ->bind(':value', $value, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $result = $db->loadResult();
            } catch (\Exception) {
                // Ignore any errors and just return false
                return false;
            }
        }

        return $result;
    }

    /**
     * Method to remove fields from the object based on values entered in the #__content_types table.
     *
     * @param   \stdClass    $object     Object to be passed to view layout file.
     * @param   ContentType  $typeTable  Table object with content history options.
     *
     * @return  \stdClass  object with hidden fields removed.
     *
     * @since   3.2
     */
    public static function hideFields($object, ContentType $typeTable)
    {
        if ($options = json_decode($typeTable->content_history_options)) {
            if (isset($options->hideFields) && \is_array($options->hideFields)) {
                foreach ($options->hideFields as $field) {
                    unset($object->$field);
                }
            }
        }

        return $object;
    }

    /**
     * Method to load the language files for the component whose history is being viewed.
     *
     * @param   string  $typeAlias  The type alias, for example 'com_content.article'.
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function loadLanguageFiles($typeAlias)
    {
        $aliasArray = explode('.', $typeAlias);

        if (\is_array($aliasArray) && \count($aliasArray) == 2) {
            $component = ($aliasArray[1] == 'category') ? 'com_categories' : $aliasArray[0];
            $lang      = Factory::getLanguage();

            /**
             * Loading language file from the administrator/language directory then
             * loading language file from the administrator/components/extension/language directory
             */
            $lang->load($component, JPATH_ADMINISTRATOR)
            || $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

            // Force loading of backend global language file
            $lang->load('joomla', Path::clean(JPATH_ADMINISTRATOR));
        }
    }

    /**
     * Method to create object to pass to the layout. Format is as follows:
     * field is std object with name, value.
     *
     * Value can be a std object with name, value pairs.
     *
     * @param   \stdClass  $object      The std object from the JSON string. Can be nested 1 level deep.
     * @param   \stdClass  $formValues  Standard class of label and value in an associative array.
     *
     * @return  \stdClass  Object with translated labels where available
     *
     * @since   3.2
     */
    public static function mergeLabels($object, $formValues)
    {
        $result = new \stdClass();

        if ($object === null) {
            return $result;
        }

        $labelsArray = $formValues->labels;
        $valuesArray = $formValues->values;

        foreach ($object as $name => $value) {
            $result->$name        = new \stdClass();
            $result->$name->name  = $name;
            $result->$name->value = $valuesArray[$name] ?? $value;
            $result->$name->label = $labelsArray[$name] ?? $name;

            if (\is_object($value)) {
                $subObject = new \stdClass();

                foreach ($value as $subName => $subValue) {
                    $subObject->$subName        = new \stdClass();
                    $subObject->$subName->name  = $subName;
                    $subObject->$subName->value = $valuesArray[$subName] ?? $subValue;
                    $subObject->$subName->label = $labelsArray[$subName] ?? $subName;
                    $result->$name->value       = $subObject;
                }
            }
        }

        return $result;
    }

    /**
     * Method to prepare the object for the preview and compare views.
     *
     * @param   ContentHistory  $table  Table object loaded with data.
     *
     * @return  \stdClass  Object ready for the views.
     *
     * @since   3.2
     */
    public static function prepareData(ContentHistory $table)
    {
        $object     = static::decodeFields($table->version_data);
        $typesTable = new ContentType(Factory::getDbo());
        $typeAlias  = explode('.', $table->item_id);
        array_pop($typeAlias);
        $typesTable->load(['type_alias' => implode('.', $typeAlias)]);
        $formValues = static::getFormValues($object, $typesTable);
        $object     = static::mergeLabels($object, $formValues);
        $object     = static::hideFields($object, $typesTable);
        $object     = static::processLookupFields($object, $typesTable);

        return $object;
    }

    /**
     * Method to process any lookup values found in the content_history_options column for this table.
     * This allows category title and user name to be displayed instead of the id column.
     *
     * @param   \stdClass    $object      The std object from the JSON string. Can be nested 1 level deep.
     * @param   ContentType  $typesTable  Table object loaded with data.
     *
     * @return  \stdClass  Object with lookup values inserted.
     *
     * @since   3.2
     */
    public static function processLookupFields($object, ContentType $typesTable)
    {
        if ($options = json_decode($typesTable->content_history_options)) {
            if (isset($options->displayLookup) && \is_array($options->displayLookup)) {
                foreach ($options->displayLookup as $lookup) {
                    $sourceColumn = $lookup->sourceColumn ?? false;
                    $sourceValue  = $object->$sourceColumn->value ?? false;

                    if ($sourceColumn && $sourceValue && ($lookupValue = static::getLookupValue($lookup, $sourceValue))) {
                        $object->$sourceColumn->value = $lookupValue;
                    }
                }
            }
        }

        return $object;
    }
}
