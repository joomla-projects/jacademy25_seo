<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component Article Model
 *
 * @since  1.5
 */
class FormModel extends \Joomla\Component\Content\Administrator\Model\ArticleModel
{
    public $typeAlias = 'com_content.article';

    protected function populateState()
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $params = $app->getParams();
        $this->setState('params', $params);

        $catId = $params->get('enable_category') == 1 && $params->get('catid') ? $params->get('catid') : 0;
        $pk = $input->getInt('a_id');
        $this->setState('article.id', $pk);
        $this->setState('article.catid', $input->getInt('catid', $catId));
        $return = $input->get('return', '', 'base64');
        $this->setState('return_page', base64_decode($return));
        $this->setState('layout', $input->getString('layout'));
    }

    public function getItem($itemId = null)
    {
        $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('article.id');
        $table = $this->getTable();
        $return = $table->load($itemId);

        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }

        $properties = $table->getProperties(1);
        $value      = ArrayHelper::toObject($properties, CMSObject::class);
        $value->params = new Registry($value->attribs);
        
        // Compute permissions
        $user   = $this->getCurrentUser();
        $userId = $user->id;
        $asset  = 'com_content.article.' . $value->id;

        if ($user->authorise('core.edit', $asset)) {
            $value->params->set('access-edit', true);
        } elseif ($userId && $user->authorise('core.edit.own', $asset) && $userId == $value->created_by) {
            $value->params->set('access-edit', true);
        }

        $value->articletext = $value->introtext;
        if (!empty($value->fulltext)) {
            $value->articletext .= '<hr id="system-readmore">' . $value->fulltext;
        }

        return $value;
    }

    public function save($data)
    {
        if (Associations::isEnabled() && !empty($data['id']) && $associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $data['id'])) {
            foreach ($associations as $tag => $associated) {
                $associations[$tag] = (int) $associated->id;
            }
            $data['associations'] = $associations;
        }

        if (!Multilanguage::isEnabled()) {
            $data['language'] = '*';
        }

        return parent::save($data);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);
        if (empty($form)) {
            return false;
        }

        $app  = Factory::getApplication();
        $user = $app->getIdentity();
        $id = (int) $this->getState('article.id', $app->getInput()->getInt('a_id'));

        if ($id > 0 && !$user->authorise('core.edit.state', 'com_content.article.' . $id)) {
            $form->setFieldAttribute('catid', 'readonly', 'true');
            $form->setFieldAttribute('catid', 'required', 'false');
            $form->setFieldAttribute('catid', 'filter', 'unset');
        }

        return $form;
    }

    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $params = $this->getState()->get('params');

        if ($params && $params->get('enable_category') == 1 && $params->get('catid')) {
            $form->setFieldAttribute('catid', 'default', $params->get('catid'));
            $form->setFieldAttribute('catid', 'readonly', 'true');
        }

        if (!Multilanguage::isEnabled()) {
            $form->setFieldAttribute('language', 'type', 'hidden');
            $form->setFieldAttribute('language', 'default', '*');
        }

        parent::preprocessForm($form, $data, $group);
    }

    public function getTable($name = 'Article', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
