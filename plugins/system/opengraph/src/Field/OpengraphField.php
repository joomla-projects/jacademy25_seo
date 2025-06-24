<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Opengraph\Field;

use Joomla\CMS\Extension\Component;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Opengraph\OpengraphServiceInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @since  1.7.0
 */

class OpengraphField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Opengraph';


    /**
     * Method to get the field options.
     *
     * @return  object[]  The field option objects.
     *
     * @since   3.7.0
     */
    protected function getOptions()
    {

        $app =  Factory::getApplication();
        $options   = parent::getOptions();
        //todo : make this more modular or flexible
        $component = $app->bootComponent('com_content');
        if (!$component instanceof OpengraphServiceInterface) {
            return $options;
        }

        $fields = $component->getOpengraphFields();
        foreach ($fields as $value => $text) {
            $options[] = HTMLHelper::_('select.option', $value, $text);
        }

        return $options;
    }
}
