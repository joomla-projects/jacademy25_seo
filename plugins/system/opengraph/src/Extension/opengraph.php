<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Opengraph
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Opengraph\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * OpenGraph Metadata plugin.
 *
 * @since  __DEPLOY_VERSION__
 */

final class Opengraph extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead' => 'onBeforeCompileHead',
            'onContentPrepareForm' => 'onContentPrepareForm',
        ];
    }

    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  __DEPLOY_VERSION__
     */
    protected $app;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Method to handle the onBeforeCompileHead event
     *
     * @param   BeforeCompileHeadEvent  $event  The event object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onBeforeCompileHead(BeforeCompileHeadEvent $event)
    {
        $app = $this->getApplication();

        // Only run in frontend
        if (!$app->isClient('site')) {
            return;
        }

        // Will add OpenGraph meta tags logic here in the future
        // This method will be called to generate OpenGraph tags
    }

    /**
     * Method to add OpenGraph fields to content forms
     *
     * @param   PrepareFormEvent  $event  The event object.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $app = $this->getApplication();

        if (!$app->isClient('administrator')) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();

        $name = $form->getName();



        // Check if it's the article form
        if ($name === 'com_content.article') {
            $xmlFile = __DIR__ . '/../Forms/Article/Article.xml';
            if (file_exists($xmlFile)) {
                $result = $form->loadFile($xmlFile, false);

            }
        }

        // Check if it's the category form
        if (strpos($name, 'com_categories.category') === 0) {
            $extension = '';

            // Extract extension from form name if it's concatenated
            if ($name === 'com_categories.categorycom_content') {
                $extension = 'com_content';
            } else {
                // Try to get extension from different sources
                if (is_object($data) && isset($data->extension)) {
                    $extension = $data->extension;
                } elseif (is_array($data) && isset($data['extension'])) {
                    $extension = $data['extension'];
                } else {
                    $extension = $form->getValue('extension');
                }

                // If still empty, try from request
                if (empty($extension)) {
                    $input = $this->getApplication()->input;
                    $extension = $input->get('extension', '', 'cmd');
                }
            }



            // Load form only for content categories
            if ($extension === 'com_content') {
                $xmlFile = __DIR__ . '/../Forms/Category/Category.xml';
                if (file_exists($xmlFile)) {
                    $result = $form->loadFile($xmlFile, false);

                }
            }
        }
    }
}
