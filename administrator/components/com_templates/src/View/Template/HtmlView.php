<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\View\Template;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Templates\Administrator\Model\TemplateModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a template.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The template details
     *
     * @var  \stdClass|false
     */
    protected $template;

    /**
     * For loading the source form
     *
     * @var  Form
     */
    protected $form;

    /**
     * For loading source file contents
     *
     * @var  array
     */
    protected $source;

    /**
     * Extension id
     *
     * @var  integer
     */
    protected $id;

    /**
     * Encrypted file path
     *
     * @var  string
     */
    protected $file;

    /**
     * List of available overrides
     *
     * @var   array
     */
    protected $overridesList;

    /**
     * Name of the present file
     *
     * @var  string
     */
    protected $fileName;

    /**
     * Type of the file - image, source, font
     *
     * @var  string
     */
    protected $type;

    /**
     * For loading image information
     *
     * @var  array
     */
    protected $image;

    /**
     * Template id for showing preview button
     *
     * @var  \stdClass
     */
    protected $preview;

    /**
     * For loading font information
     *
     * @var  array
     */
    protected $font;

    /**
     * A nested array containing list of files and folders
     *
     * @var  array
     */
    protected $files;

    /**
     * An array containing a list of compressed files
     *
     * @var  array
     */
    protected $archive;

    /**
     * The state of installer override plugin.
     *
     * @var  array
     *
     * @since  4.0.0
     */
    protected $pluginState;

    /**
     * A nested array containing list of files and folders in the media folder
     *
     * @var  array
     *
     * @since  4.1.0
     */
    protected $mediaFiles;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void|boolean
     */
    public function display($tpl = null)
    {
        /** @var TemplateModel $model */
        $model = $this->getModel();

        $app               = Factory::getApplication();
        $this->file        = $app->getInput()->get('file', '');
        $this->fileName    = InputFilter::getInstance()->clean(base64_decode($this->file), 'string');
        $explodeArray      = explode('.', $this->fileName);
        $ext               = end($explodeArray);
        $this->files       = $model->getFiles();
        $this->mediaFiles  = $model->getMediaFiles();
        $this->state       = $model->getState();
        $this->template    = $model->getTemplate();
        $this->preview     = $model->getPreview();
        $this->pluginState = PluginHelper::isEnabled('installer', 'override');
        $this->updatedList = $model->getUpdatedList();
        $this->styles      = $model->getAllTemplateStyles();
        $this->stylesHTML  = '';

        $params       = ComponentHelper::getParams('com_templates');
        $imageTypes   = explode(',', $params->get('image_formats', 'gif,bmp,jpg,jpeg,png,webp'));
        $sourceTypes  = explode(',', $params->get('source_formats', 'txt,less,ini,xml,js,php,css,scss,sass,json'));
        $fontTypes    = explode(',', $params->get('font_formats', 'woff,woff2,ttf,otf'));
        $archiveTypes = explode(',', $params->get('compressed_formats', 'zip'));

        if (\in_array($ext, $sourceTypes)) {
            $this->form   = $model->getForm();
            $this->form->setFieldAttribute('source', 'syntax', $ext);
            $this->source = $model->getSource();
            $this->type   = 'file';
        } elseif (\in_array($ext, $imageTypes)) {
            try {
                $this->image = $model->getImage();
                $this->type  = 'image';
            } catch (\RuntimeException) {
                $app->enqueueMessage(Text::_('COM_TEMPLATES_GD_EXTENSION_NOT_AVAILABLE'));
                $this->type = 'home';
            }
        } elseif (\in_array($ext, $fontTypes)) {
            $this->font = $model->getFont();
            $this->type = 'font';
        } elseif (\in_array($ext, $archiveTypes)) {
            $this->archive = $model->getArchive();
            $this->type    = 'archive';
        } else {
            $this->type = 'home';
        }

        $this->overridesList = $model->getOverridesList();
        $this->id            = $this->state->get('extension.id');

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            $app->enqueueMessage(implode("\n", $errors));

            return false;
        }

        $this->addToolbar();

        if (!$this->getCurrentUser()->authorise('core.admin')) {
            $this->setLayout('readonly');
        }

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     *
     * @return  void
     */
    protected function addToolbar()
    {
        $app     = Factory::getApplication();
        $user    = $this->getCurrentUser();
        $toolbar = $this->getDocument()->getToolbar();
        $app->getInput()->set('hidemainmenu', true);

        // User is global SuperUser
        $isSuperUser  = $user->authorise('core.admin');

        ToolbarHelper::title(Text::sprintf('COM_TEMPLATES_MANAGER_VIEW_TEMPLATE', ucfirst($this->template->name)), 'icon-code thememanager');

        // Add a Template preview button
        if ($this->type === 'home') {
            $client = (int) $this->preview->client_id === 1 ? 'administrator/' : '';
            $toolbar->linkButton('preview', 'COM_TEMPLATES_BUTTON_PREVIEW')
                ->url(Uri::root() . $client . 'index.php?tp=1&templateStyle=' . $this->preview->id)
                ->icon('icon-image')
                ->attributes(['target' => '_new']);
        }

        // Only show file edit buttons for global SuperUser
        if ($isSuperUser) {
            switch ($this->type) {
                case 'file':
                    $toolbar->apply('template.apply');
                    $toolbar->save('template.save');
                    $toolbar->cancel('template.close', 'COM_TEMPLATES_BUTTON_CLOSE_FILE');
                    break;

                case 'image':
                    // Add a Crop and Resize button
                    $toolbar->standardButton('crop', 'COM_TEMPLATES_BUTTON_CROP', 'template.cropImage')
                        ->listCheck(false)
                        ->icon('icon-crop');
                    ToolbarHelper::modal('resizeModal', 'icon-expand', 'COM_TEMPLATES_BUTTON_RESIZE');
                    $toolbar->cancel('template.close', 'COM_TEMPLATES_BUTTON_CLOSE_FILE');
                    break;

                case 'archive':
                    // Add an extract button
                    $toolbar->standardButton('extract', 'COM_TEMPLATES_BUTTON_EXTRACT_ARCHIVE', 'template.extractArchive')
                        ->listCheck(false)
                        ->icon('icon-chevron-down');
                    break;

                case 'home':
                    // Add a copy/child template button
                    if (isset($this->template->xmldata->inheritable) && (string) $this->template->xmldata->inheritable === '1') {
                        ToolbarHelper::modal('childModal', 'icon-copy', 'COM_TEMPLATES_BUTTON_TEMPLATE_CHILD');
                    } elseif (empty($this->template->xmldata->parent) && empty($this->template->xmldata->namespace)) {
                        // We can't copy parent templates nor namespaced templates
                        ToolbarHelper::modal('copyModal', 'icon-copy', 'COM_TEMPLATES_BUTTON_COPY_TEMPLATE');
                    }

                    break;
            }
        }

        // Only show file manage buttons for global SuperUser
        if ($isSuperUser) {
            if ($this->type === 'home') {
                // Add Manage folders button
                ToolbarHelper::modal('folderModal', 'icon-folder icon white', 'COM_TEMPLATES_BUTTON_FOLDERS');

                // Add a new file button
                ToolbarHelper::modal('fileModal', 'icon-file', 'COM_TEMPLATES_BUTTON_FILE');
            } else {
                // Add a Rename file Button
                ToolbarHelper::modal('renameModal', 'icon-sync', 'COM_TEMPLATES_BUTTON_RENAME_FILE');

                // Add a Delete file Button
                ToolbarHelper::modal('deleteModal', 'icon-trash', 'COM_TEMPLATES_BUTTON_DELETE_FILE', 'btn-danger');
            }
        }

        if (\count($this->updatedList) !== 0 && $this->pluginState && $this->type === 'home') {
            /** @var DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('override-group', 'COM_TEMPLATES_BUTTON_CHECK')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->form('updateForm')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('template.publish')
                ->text('COM_TEMPLATES_BUTTON_CHECK_LIST_ENTRY')
                ->form('updateForm')
                ->listCheck(true);
            $childBar->unpublish('template.unpublish')
                ->text('COM_TEMPLATES_BUTTON_UNCHECK_LIST_ENTRY')
                ->form('updateForm')
                ->listCheck(true);
            $childBar->unpublish('template.deleteOverrideHistory')
                ->text('COM_TEMPLATES_BUTTON_DELETE_LIST_ENTRY')
                ->form('updateForm')
                ->listCheck(true);
        }

        if (!\in_array($this->type, ['image', 'file'])) {
            $toolbar->cancel('template.cancel');
        }

        $toolbar->divider();
        $toolbar->help('Templates:_Customise');
    }

    /**
     * Method for creating the collapsible tree.
     *
     * @param   array  $array  The value of the present node for recursion
     *
     * @return  string
     *
     * @note    Uses recursion
     * @since   3.2
     */
    protected function directoryTree($array)
    {
        $temp        = $this->files;
        $this->files = $array;
        $txt         = $this->loadTemplate('tree');
        $this->files = $temp;

        return $txt;
    }

    /**
     * Method for listing the folder tree in modals.
     *
     * @param   array  $array  The value of the present node for recursion
     *
     * @return  string
     *
     * @note    Uses recursion
     * @since   3.2
     */
    protected function folderTree($array)
    {
        $temp        = $this->files;
        $this->files = $array;
        $txt         = $this->loadTemplate('folders');
        $this->files = $temp;

        return $txt;
    }

    /**
     * Method for creating the collapsible tree.
     *
     * @param   array  $array  The value of the present node for recursion
     *
     * @return  string
     *
     * @note    Uses recursion
     * @since   4.1.0
     */
    protected function mediaTree($array)
    {
        $temp             = $this->mediaFiles;
        $this->mediaFiles = $array;
        $txt              = $this->loadTemplate('tree_media');
        $this->mediaFiles = $temp;

        return $txt;
    }

    /**
     * Method for listing the folder tree in modals.
     *
     * @param   array  $array  The value of the present node for recursion
     *
     * @return  string
     *
     * @note    Uses recursion
     * @since   4.1.0
     */
    protected function mediaFolderTree($array)
    {
        $temp             = $this->mediaFiles;
        $this->mediaFiles = $array;
        $txt              = $this->loadTemplate('media_folders');
        $this->mediaFiles = $temp;

        return $txt;
    }
}
