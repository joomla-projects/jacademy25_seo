<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Module table
 *
 * @since  1.5
 */
class Module extends Table
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Constructor.
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.5
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__modules', 'id', $db, $dispatcher);

        $this->access = (int) Factory::getApplication()->get('access');
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since   3.2
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_modules.module.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   3.2
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    /**
     * Method to get the parent asset id for the record
     *
     * @param   ?Table    $table  A Table object (optional) for the asset parent
     * @param   ?integer  $id     The id (optional) of the content.
     *
     * @return  integer
     *
     * @since   3.2
     */
    protected function _getAssetParentId(?Table $table = null, $id = null)
    {
        $assetId = null;

        // This is a module that needs to parent with the extension.
        if ($assetId === null) {
            // Build the query to get the asset id of the parent component.
            $query = $this->_db->getQuery(true)
                ->select($this->_db->quoteName('id'))
                ->from($this->_db->quoteName('#__assets'))
                ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote('com_modules'));

            // Get the asset id from the database.
            $this->_db->setQuery($query);

            if ($result = $this->_db->loadResult()) {
                $assetId = (int) $result;
            }
        }

        // Return the asset id.
        if ($assetId) {
            return $assetId;
        }

        return parent::_getAssetParentId($table, $id);
    }

    /**
     * Overloaded check function.
     *
     * @return  boolean  True if the instance is sane and able to be stored in the database.
     *
     * @see     Table::check()
     * @since   1.5
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Check for valid name
        if (trim($this->title) === '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_MODULE'));

            return false;
        }

        // Set publish_up, publish_down to null if not set
        if (!$this->publish_up) {
            $this->publish_up = null;
        }

        if (!$this->publish_down) {
            $this->publish_down = null;
        }

        // Prevent to save too large content > 65535
        if ((!empty($this->content) && \strlen($this->content) > 65535) || (!empty($this->params) && \strlen($this->params) > 65535)) {
            $this->setError(Text::_('COM_MODULES_FIELD_CONTENT_TOO_LARGE'));

            return false;
        }

        // Check the publish down date is not earlier than publish up.
        if ((int) $this->publish_down > 0 && $this->publish_down < $this->publish_up) {
            // Swap the dates.
            $temp               = $this->publish_up;
            $this->publish_up   = $this->publish_down;
            $this->publish_down = $temp;
        }

        return true;
    }

    /**
     * Overloaded bind function.
     *
     * @param   array  $array   Named array.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  mixed  Null if operation was satisfactory, otherwise returns an error
     *
     * @see     Table::bind()
     * @since   1.5
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && \is_array($array['params'])) {
            $registry        = new Registry($array['params']);
            $array['params'] = (string) $registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && \is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Stores a module.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   3.7.0
     */
    public function store($updateNulls = true)
    {
        if (!$this->ordering) {
            $this->ordering = $this->getNextOrder($this->_db->quoteName('position') . ' = ' . $this->_db->quote($this->position));
        }

        return parent::store($updateNulls);
    }
}
