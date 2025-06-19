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
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Uri\Uri;




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
     * The application object.
     *
     * @var CMSApplication
     */
    protected $app;

    /**
     * Should the plugin autoload its language files.
     *
     * @var bool
     */
    protected $autoloadLanguage = true;

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
     * Handle the beforeCompileHead event.
     *
     * @param BeforeCompileHeadEvent $event
     *
     * @return void
     */
    public function onBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        $input = $app->input;
        $option = $input->get('option', '', 'cmd');
        $view = $input->get('view', '', 'cmd');
        $id = $input->getInt('id');


        // @todo: will be changed in future to support other components

        if ($option !== 'com_content' || $view !== 'article' || !$id) {
            return;
        }

        $table = Table::getInstance('Content');
        if (!$table->load($id)) {
            return;
        }

        $attribs = new Registry($table->attribs);

        if ($attribs->get('og_enabled', '') === '0') {
            return;
        }

        $document = $this->app->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $this->injectOpenGraphData($document, $attribs);
    }

    /**
     * Handle the onContentPrepareForm event.
     *
     * @param PrepareFormEvent $event
     *
     * @return void
     */
    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $app = $this->getApplication();

        if (!$app->isClient('administrator')) {
            return;
        }

        $form = $event->getForm();
        $name = $form->getName();

        //todo : replace with interface check
        $supportedForms = [
            'com_content.article',
            'com_categories.categorycom_content',
            'com_menus.item'
        ];

        if (!in_array($name, $supportedForms, true)) {
            return;
        }

        $xml = __DIR__ . '/../forms/opengraph.xml';
        if (file_exists($xml)) {
            $form->loadFile($xml, false);
        }
    }

    /**
     * Inject the OpenGraph data into the document.
     *
     * @param Document $document
     * @param Registry $params
     *
     * @return void
     */
    private function injectOpenGraphData(Document $document, Registry $params): void
    {


        $this->setMetaData($document, 'og:title', $params->get('og_title'), 'property');
        $this->setMetaData($document, 'og:description', $params->get('og_description'), 'property');
        $this->setMetaData($document, 'og:type', $params->get('og_type'), 'property');


        $this->setMetaData($document, 'twitter:card', $params->get('twitter_card'), 'name');
        $this->setMetaData($document, 'twitter:title', $params->get('twitter_title'), 'name');
        $this->setMetaData($document, 'twitter:description', $params->get('twitter_description'), 'name');

        $this->setMetaData($document, 'fb:app_id', $params->get('fb_app_id'), 'property');

        $this->setOpenGraphImage(
            $document,
            $params->get('og_image'),
            $params->get('og_image_alt'),
            Uri::base()
        );
    }

    /**
     * Set metadata tag in document.
     *
     * @param Document $document
     * @param string $name
     * @param string|null $value
     * @param string $attributeType
     *
     * @return void
     */
    private function setMetaData(Document $document, string $name, ?string $value, string $attributeType): void
    {
        if (!empty($value)) {
            $document->setMetaData($name, $value, $attributeType);
        }
    }

    /**
     * Set OpenGraph Image tag in document.
     *
     * @param Document $document
     * @param string|null $image
     * @param string|null $alt
     * @param string|null $baseUrl
     *
     * @return void
     */
    private function setOpenGraphImage(Document $document, ?string $image, ?string $alt = '', ?string $baseUrl = ''): void
    {
        if (empty($image)) {
            return;
        }

        $url = rtrim((string) $baseUrl, '/') . '/' . ltrim($image, '/');

        $this->setMetaData($document, 'og:image', $url, 'property');
        $this->setMetaData($document, 'og:image:alt', $alt, 'property');
        $this->setMetaData($document, 'twitter:image', $url, 'name');
        $this->setMetaData($document, 'twitter:image:alt', $alt, 'name');
    }
}
