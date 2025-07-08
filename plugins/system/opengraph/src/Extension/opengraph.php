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
use Joomla\CMS\Opengraph\OpengraphServiceInterface;
use Joomla\CMS\Uri\Uri;
use Exception;




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
        $form    = $event->getForm();
        $context = $form->getName();
        $app     = $this->getApplication();
        if (!$app->isClient('administrator') || !$this->isSupported($context)) {
            return;
        }

        $isCategory = $context === 'com_categories.categorycom_content';
        $isMenu = $context === 'com_menus.item';

        $groupName  =  $isMenu ? 'params' : 'attribs';

        // Load opengraphmappings.xml for categories directly no need to adjust fields group
        if ($isCategory) {
            try {
                $form::addFormPath(__DIR__ . '/../forms');
                $form->loadFile('opengraphmappings', false);
            } catch (Exception $e) {
                error_log('OpenGraph Plugin: Failed to load mappings form: ' . $e->getMessage());
            }

            return;
        }

        // Load and modify opengraph.xml for articles and menus
        $mainXml = __DIR__ . '/../forms/opengraph.xml';
        if (file_exists($mainXml)) {
            try {
                $modifiedXml = $this->adjustFieldsGroup($mainXml, $groupName);
                $form->load($modifiedXml, false);
            } catch (Exception $e) {
                error_log('OpenGraph Plugin: Failed to load main form: ' . $e->getMessage());
            }
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


    /**
     * Check if the current plugin should execute opengraph related activities
     *
     * @param   string  $context
     *
     * @return   boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function isSupported($context): bool
    {
        $parts = explode('.', $context, 2);
        if (empty($parts)) {
            return false;
        }

        if ($parts[0] === 'com_categories' && isset($parts[1])) {
            // Extract true component name from com_categories context
            if (preg_match('/com_[a-zA-Z0-9_]+$/', $parts[1], $matches)) {
                $componentName = $matches[0];
            } else {
                return false;
            }
        } else {
            $componentName = $parts[0];
        }

        try {
            $component = $this->getApplication()->bootComponent($componentName);
        } catch (Exception $e) {
            error_log('OpenGraph Plugin: Failed to boot component: ' . $e->getMessage());
            return false;
        }

        return $component instanceof OpengraphServiceInterface;
    }


    private function adjustFieldsGroup(string $filePath, string $newGroup): string
    {
        $xmlContent = file_get_contents($filePath);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            throw new Exception("Could not load XML file: {$filePath}");
        }

        // Adjust all <fields> nodes to use the desired group
        foreach ($xml->xpath('//fields') as $fields) {
            $fields['name'] = $newGroup;
        }

        return $xml->asXML();
    }
}
