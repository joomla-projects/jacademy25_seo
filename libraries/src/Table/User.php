<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Users table
 *
 * @since  1.7.0
 */
class User extends Table
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Associative array of group ids => group ids for the user
     *
     * @var    array
     * @since  1.7.0
     */
    public $groups;

    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since  1.7.0
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__users', 'id', $db, $dispatcher);

        // Initialise.
        $this->id        = 0;
        $this->sendEmail = 0;
    }

    /**
     * Method to load a user, user groups, and any other necessary data
     * from the database so that it can be bound to the user object.
     *
     * @param   integer  $userId  An optional user id.
     * @param   boolean  $reset   False if row not found or on error
     *                           (internal error state set in that case).
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.7.0
     */
    public function load($userId = null, $reset = true)
    {
        // Get the id to load.
        if ($userId !== null) {
            $this->id = $userId;
        } else {
            $userId = $this->id;
        }

        // Check for a valid id to load.
        if ($userId === null) {
            return false;
        }

        // Reset the table.
        $this->reset();

        $userId = (int) $userId;

        // Load the user data.
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_db->quoteName('#__users'))
            ->where($this->_db->quoteName('id') . ' = :userid')
            ->bind(':userid', $userId, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $data = (array) $this->_db->loadAssoc();

        if (!\count($data)) {
            return false;
        }

        // Convert email from punycode
        $data['email'] = PunycodeHelper::emailToUTF8($data['email']);

        // Bind the data to the table.
        $return = $this->bind($data);

        if ($return !== false) {
            // Load the user groups.
            $query->clear()
                ->select($this->_db->quoteName('g.id'))
                ->select($this->_db->quoteName('g.title'))
                ->from($this->_db->quoteName('#__usergroups', 'g'))
                ->join(
                    'INNER',
                    $this->_db->quoteName('#__user_usergroup_map', 'm'),
                    $this->_db->quoteName('m.group_id') . ' = ' . $this->_db->quoteName('g.id')
                )
                ->where($this->_db->quoteName('m.user_id') . ' = :muserid')
                ->bind(':muserid', $userId, ParameterType::INTEGER);
            $this->_db->setQuery($query);

            // Add the groups to the user data.
            $this->groups = $this->_db->loadAssocList('id', 'id');
        }

        return $return;
    }

    /**
     * Method to bind the user, user groups, and any other necessary data.
     *
     * @param   array  $array   The data to bind.
     * @param   mixed  $ignore  An array or space separated list of fields to ignore.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.7.0
     */
    public function bind($array, $ignore = '')
    {
        if (\array_key_exists('params', $array) && \is_array($array['params'])) {
            $registry        = new Registry($array['params']);
            $array['params'] = (string) $registry;
        }

        // Attempt to bind the data.
        $return = parent::bind($array, $ignore);

        // Load the real group data based on the bound ids.
        if ($return && !empty($this->groups)) {
            // Set the group ids.
            $this->groups = ArrayHelper::toInteger($this->groups);

            // Get the titles for the user groups.
            $query = $this->_db->getQuery(true)
                ->select($this->_db->quoteName('id'))
                ->select($this->_db->quoteName('title'))
                ->from($this->_db->quoteName('#__usergroups'))
                ->whereIn($this->_db->quoteName('id'), array_values($this->groups));
            $this->_db->setQuery($query);

            // Set the titles for the user groups.
            $this->groups = $this->_db->loadAssocList('id', 'id');
        }

        return $return;
    }

    /**
     * Validation and filtering
     *
     * @return  boolean  True if satisfactory
     *
     * @since   1.7.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Set user id to null instead of 0, if needed
        if ($this->id === 0) {
            $this->id = null;
        }

        $filterInput = InputFilter::getInstance();

        // Validate user information
        if ($filterInput->clean($this->name, 'TRIM') == '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME'));

            return false;
        }

        if ($filterInput->clean($this->username, 'TRIM') == '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME'));

            return false;
        }

        if (
            preg_match('#[<>"\'%;()&\\\\]|\\.\\./#', $this->username) || StringHelper::strlen($this->username) < 2
            || $filterInput->clean($this->username, 'TRIM') !== $this->username || StringHelper::strlen($this->username) > 150
        ) {
            $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_VALID_AZ09', 2));

            return false;
        }

        if (
            ($filterInput->clean($this->email, 'TRIM') == '') || !MailHelper::isEmailAddress($this->email)
            || StringHelper::strlen($this->email) > 100
        ) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_VALID_MAIL'));

            return false;
        }

        // Convert email to punycode for storage
        $this->email = PunycodeHelper::emailToPunycode($this->email);

        // Set the registration timestamp
        if (empty($this->registerDate)) {
            $this->registerDate = Factory::getDate()->toSql();
        }

        // Set the lastvisitDate timestamp
        if (empty($this->lastvisitDate)) {
            $this->lastvisitDate = null;
        }

        // Set the lastResetTime timestamp
        if (empty($this->lastResetTime)) {
            $this->lastResetTime = null;
        }

        $uid = (int) $this->id;

        // Check for existing username
        $query = $this->_db->getQuery(true)
            ->select($this->_db->quoteName('id'))
            ->from($this->_db->quoteName('#__users'))
            ->where($this->_db->quoteName('username') . ' = :username')
            ->where($this->_db->quoteName('id') . ' != :userid')
            ->bind(':username', $this->username)
            ->bind(':userid', $uid, ParameterType::INTEGER);
        $this->_db->setQuery($query);

        $xid = (int) $this->_db->loadResult();

        if ($xid && $xid != (int) $this->id) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_USERNAME_INUSE'));

            return false;
        }

        // Check for existing email
        $query->clear()
            ->select($this->_db->quoteName('id'))
            ->from($this->_db->quoteName('#__users'))
            ->where('LOWER(' . $this->_db->quoteName('email') . ') = LOWER(:mail)')
            ->where($this->_db->quoteName('id') . ' != :muserid')
            ->bind(':mail', $this->email)
            ->bind(':muserid', $uid, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $xid = (int) $this->_db->loadResult();

        if ($xid && $xid != (int) $this->id) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_EMAIL_INUSE'));

            return false;
        }

        // Check for root_user != username
        $rootUser = Factory::getApplication()->get('root_user');

        if (!is_numeric($rootUser)) {
            $query->clear()
                ->select($this->_db->quoteName('id'))
                ->from($this->_db->quoteName('#__users'))
                ->where($this->_db->quoteName('username') . ' = :username')
                ->bind(':username', $rootUser);
            $this->_db->setQuery($query);
            $xid = (int) $this->_db->loadResult();

            if (
                $rootUser == $this->username && (!$xid || $xid && $xid != (int) $this->id)
                || $xid && $xid == (int) $this->id && $rootUser != $this->username
            ) {
                $this->setError(Text::_('JLIB_DATABASE_ERROR_USERNAME_CANNOT_CHANGE'));

                return false;
            }
        }

        // Set an empty string value to the legacy otpKey and otep columns if empty
        $this->otpKey = $this->otpKey ?: '';
        $this->otep   = $this->otep ?: '';

        return true;
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     *
     * If a primary key value is set the row with that primary key value will be updated with the instance property values.
     * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     */
    public function store($updateNulls = true)
    {
        // Get the table key and key value.
        $k   = $this->_tbl_key;
        $key = $this->$k;

        // @todo: This is a dumb way to handle the groups.
        // Store groups locally so as to not update directly.
        $groups = $this->groups;
        unset($this->groups);

        // Insert or update the object based on presence of a key value.
        if ($key) {
            // Already have a table key, update the row.
            $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
        } else {
            // Don't have a table key, insert the row.
            $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        // Reset groups to the local object.
        $this->groups = $groups;

        $query = $this->_db->getQuery(true);

        // Store the group data if the user data was saved.
        if (\is_array($this->groups) && \count($this->groups)) {
            $uid = (int) $this->id;

            // Grab all usergroup entries for the user
            $query->clear()
                ->select($this->_db->quoteName('group_id'))
                ->from($this->_db->quoteName('#__user_usergroup_map'))
                ->where($this->_db->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $uid, ParameterType::INTEGER);

            $this->_db->setQuery($query);
            $result = $this->_db->loadObjectList();

            // Loop through them and check if database contains something $this->groups does not
            if (\count($result)) {
                $mapGroupId = [];

                foreach ($result as $map) {
                    if (\array_key_exists($map->group_id, $this->groups)) {
                        // It already exists, no action required
                        unset($groups[$map->group_id]);
                    } else {
                        $mapGroupId[] = (int) $map->group_id;
                    }
                }

                if (\count($mapGroupId)) {
                    $query->clear()
                        ->delete($this->_db->quoteName('#__user_usergroup_map'))
                        ->where($this->_db->quoteName('user_id') . ' = :uid')
                        ->whereIn($this->_db->quoteName('group_id'), $mapGroupId)
                        ->bind(':uid', $uid, ParameterType::INTEGER);

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }
            }

            // If there is anything left in this->groups it needs to be inserted
            if (\count($groups)) {
                // Set the new user group maps.
                $query->clear()
                    ->insert($this->_db->quoteName('#__user_usergroup_map'))
                    ->columns([$this->_db->quoteName('user_id'), $this->_db->quoteName('group_id')]);

                foreach ($groups as $group) {
                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$this->id , $group],
                                [ParameterType::INTEGER, ParameterType::INTEGER]
                            )
                        )
                    );
                }

                $this->_db->setQuery($query);
                $this->_db->execute();
            }

            unset($groups);
        }

        // If a user is blocked, delete the cookie login rows
        if ($this->block == 1) {
            $query->clear()
                ->delete($this->_db->quoteName('#__user_keys'))
                ->where($this->_db->quoteName('user_id') . ' = :user_id')
                ->bind(':user_id', $this->username);
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return true;
    }

    /**
     * Method to delete a user, user groups, and any other necessary data from the database.
     *
     * @param   integer  $userId  An optional user id.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.7.0
     */
    public function delete($userId = null)
    {
        // Set the primary key to delete.
        $k = $this->_tbl_key;

        if ($userId) {
            $this->$k = (int) $userId;
        }

        $key = (int) $this->$k;

        // Delete the user.
        $query = $this->_db->getQuery(true)
            ->delete($this->_db->quoteName($this->_tbl))
            ->where($this->_db->quoteName($this->_tbl_key) . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete the user group maps.
        $query->clear()
            ->delete($this->_db->quoteName('#__user_usergroup_map'))
            ->where($this->_db->quoteName('user_id') . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        /*
         * Clean Up Related Data.
         */

        $query->clear()
            ->delete($this->_db->quoteName('#__messages_cfg'))
            ->where($this->_db->quoteName('user_id') . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        $query->clear()
            ->delete($this->_db->quoteName('#__messages'))
            ->where($this->_db->quoteName('user_id_to') . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        $query->clear()
            ->delete($this->_db->quoteName('#__user_keys'))
            ->where($this->_db->quoteName('user_id') . ' = :username')
            ->bind(':username', $this->username);
        $this->_db->setQuery($query);
        $this->_db->execute();

        return true;
    }

    /**
     * Updates last visit time of user
     *
     * @param   integer  $timeStamp  The timestamp, defaults to 'now'.
     * @param   integer  $userId     The user id (optional).
     *
     * @return  boolean  False if an error occurs
     *
     * @since   1.7.0
     */
    public function setLastVisit($timeStamp = null, $userId = null)
    {
        // Check for User ID
        if (\is_null($userId)) {
            if (isset($this)) {
                $userId = $this->id;
            } else {
                jexit('No userid in setLastVisit');
            }
        }

        // If no timestamp value is passed to function, then current time is used.
        if ($timeStamp === null) {
            $timeStamp = 'now';
        }

        $date      = Factory::getDate($timeStamp);
        $userId    = (int) $userId;
        $lastVisit = $date->toSql();

        // Update the database row for the user.
        $db    = $this->_db;
        $query = $db->getQuery(true)
            ->update($db->quoteName($this->_tbl))
            ->set($db->quoteName('lastvisitDate') . ' = :lastvisitDate')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':lastvisitDate', $lastVisit)
            ->bind(':id', $userId, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        return true;
    }
}
