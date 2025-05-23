<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\UCM;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Table\TableInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for implementing UCM
 *
 * @since  3.1
 */
class UCMBase implements UCM
{
    /**
     * The UCM type object
     *
     * @var    UCMType
     * @since  3.1
     */
    protected $type;

    /**
     * The alias for the content table
     *
     * @var    string
     * @since  3.1
     */
    protected $alias;

    /**
     * Instantiate the UCMBase.
     *
     * @param   string    $alias  The alias string
     * @param   ?UCMType  $type   The type object
     *
     * @since   3.1
     */
    public function __construct($alias = null, ?UCMType $type = null)
    {
        // Setup dependencies.
        $input       = Factory::getApplication()->getInput();
        $this->alias = $alias ?: $input->get('option') . '.' . $input->get('view');

        $this->type = $type ?: $this->getType();
    }

    /**
     * Store data to the appropriate table
     *
     * @param   array            $data        Data to be stored
     * @param   ?TableInterface  $table       Table Object
     * @param   string           $primaryKey  The primary key name
     *
     * @return  boolean  True on success
     *
     * @since   3.1
     * @throws  \Exception
     */
    protected function store($data, ?TableInterface $table = null, $primaryKey = null)
    {
        if (!$table) {
            $table = new \Joomla\CMS\Table\Ucm(Factory::getDbo());
        }

        $ucmId      = $data['ucm_id'] ?? null;
        $primaryKey = $primaryKey ?: $ucmId;

        if (isset($primaryKey)) {
            $table->load($primaryKey);
        }

        try {
            $table->bind($data);
        } catch (\RuntimeException $e) {
            throw new \Exception($e->getMessage(), 500, $e);
        }

        try {
            $table->store();
        } catch (\RuntimeException $e) {
            throw new \Exception($e->getMessage(), 500, $e);
        }

        return true;
    }

    /**
     * Get the UCM Content type.
     *
     * @return  UCMType  The UCM content type
     *
     * @since   3.1
     */
    public function getType()
    {
        if (!$this->type) {
            $this->type = new UCMType($this->alias);
        }

        return $this->type;
    }

    /**
     * Method to map the base ucm fields
     *
     * @param   array     $original  Data array
     * @param   ?UCMType  $type      UCM Content Type
     *
     * @return  array  Data array of UCM mappings
     *
     * @since   3.1
     */
    public function mapBase($original, ?UCMType $type = null)
    {
        $type = $type ?: $this->type;

        $data = [
            'ucm_type_id'     => $type->id,
            'ucm_item_id'     => $original[$type->primary_key],
            'ucm_language_id' => ContentHelper::getLanguageId($original['language']),
        ];

        return $data;
    }
}
