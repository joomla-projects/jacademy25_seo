<?php

/**
 * @package     Joomla.API
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Api\Controller;

use Joomla\CMS\Language\Text;
use Joomla\Component\Joomlaupdate\Api\View\Updates\JsonapiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The updates controller
 *
 * @since  __DEPLOY_VERSION__
 */
class UpdatesController extends BaseController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $contentType = 'updates';

    /**
     * The default view for the display method.
     *
     * @var string
     * @since  __DEPLOY_VERSION__
     */
    protected $default_view = 'updates';

    /**
     * Get the latest update version for the auto updater
     *
     * @return UpdateController For chaining
     */
    public function getUpdate()
    {
        $this->validateUpdateToken();

        $view = $this->prepareView();

        $view->getUpdate();

        return $this;
    }

    /**
     * Prepare the update and output the update information
     *
     * @return UpdatesController
     *
     * @since  __DEPLOY_VERSION__
     */
    public function prepareUpdate()
    {
        $this->validateUpdateToken();

        /**
         * @var UpdateModel $model
         */
        $model = $this->getModel('Update');

        $latestVersion = $model->getAutoUpdateVersion();

        $targetVersion = $this->input->json->getString('targetVersion');

        if (!$latestVersion || $latestVersion !== $targetVersion) {
            throw new \Exception(Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_VERSION_WRONG'), 410);
        }

        $view = $this->prepareView();

        $view->prepareUpdate($targetVersion);

        return $this;
    }

    /**
     * Finalize the update
     *
     * @return UpdateController For chaining
     */
    public function finalizeUpdate()
    {
        $this->validateUpdateToken();

        $view = $this->prepareView();

        $view->finalizeUpdate();

        return $this;
    }

    /**
     * Generic method to prepare the view
     *
     * @return JsonapiView  The prepared view
     */
    protected function prepareView()
    {

        $viewType   = $this->app->getDocument()->getType();
        $viewName   = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        try {
            /** @var JsonApiView $view */
            $view = $this->getView(
                $viewName,
                $viewType,
                '',
                ['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $modelName = $this->input->get('model', $this->contentType);

        /** @var UpdateModel $model */
        $model = $this->getModel('Update', 'Administrator', ['ignore_request' => true, 'state' => $this->modelState]);

        if (!$model) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        // Push the model into the view (as default)
        $view->setModel($model, true);

        $view->setDocument($this->app->getDocument());

        return $view;
    }

    /**
     * Basic display of an item view. We don't allow this
     *
     * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function displayItem($id = null)
    {
        throw new \RuntimeException('Not implemented', 501);
    }

    /**
     * List view amended to add filtering of data. We don't allow this
     *
     * @return  static  A BaseController object to support chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function displayList()
    {
        throw new \RuntimeException('Not implemented', 501);
    }

    /**
     * Removes an item.
     *
     * @param   integer  $id  The primary key to delete item.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function delete($id = null)
    {
        throw new \RuntimeException('Not implemented', 501);
    }

    /**
     * Method to check if you can edit an existing record.
     *
     * We don't allow editing from API (yet?)
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        return false;
    }

    /**
     * Method to check if you can add a new record.
     *
     * We don't allow adding from API
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function allowAdd($data = [])
    {
        return false;
    }
}
