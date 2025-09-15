<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Opengraph
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Opengraph\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Opengraph\OpengraphServiceInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Categories\Administrator\Model\CategoryModel;
use Joomla\Component\Content\Administrator\Model\ArticleModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

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
            'onBeforeCompileHead'  => 'onBeforeCompileHead',
            'onContentPrepareForm' => 'onContentPrepareForm',
        ];
    }


    /**
     * Add fields for the OpenGraph data to the form
     *
     * @param PrepareFormEvent $event
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
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
        $isMenu     = $context === 'com_menus.item';

        $groupName  =  $isMenu ? 'params' : 'attribs';

        // Load opengraphmappings.xml for categories directly no need to adjust fields group
        if ($isCategory) {
            try {
                $form::addFormPath(__DIR__ . '/../forms');
                $form->loadFile('opengraphmappings', false);
            } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                error_log('OpenGraph Plugin: Failed to load main form: ' . $e->getMessage());
            }
        }

        // if the form is a menu item, we don't need to change placeholder values
        if ($isMenu) {
            return;
        }

        // Get the article id and category id
        $input      = $app->input;
        $articleId  = (int) ($input->getInt('id') ?: $form->getValue('id'));
        $categoryId = 0;

        if ($articleId > 0) {
            /** @var MVCComponent $articleComponent */
            $articleComponent = $app->bootComponent('com_content');
            /** @var MVCFactoryInterface $articleFactory */
            $articleFactory   = $articleComponent->getMVCFactory();

            /** @var ArticleModel $articleModel */
            $articleModel = $articleFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);
            $article      = $articleModel->getItem($articleId);

            if ($article && !empty($article->catid)) {
                $categoryId = (int) $article->catid;
            }
        }
        $catParams = new Registry();
        if ($categoryId > 0) {
            /** @var MVCComponent $catComponent */
            $catComponent = $app->bootComponent('com_categories');
            /** @var MVCFactoryInterface $catFactory */
            $catFactory   = $catComponent->getMVCFactory();

            /** @var CategoryModel $catModel */
            $catModel = $catFactory->createModel('Category', 'Administrator', ['ignore_request' => true]);
            $catModel->setState('category.id', $categoryId);

            $category  = $catModel->getItem($categoryId); // JTable row
            $catParams = new Registry($category->params ?? '{}');
        }

        if (!$catParams) {
            return;
        }
        // Get the mappings from the category params
        $mappings = [];
        foreach ($catParams as $paramKey => $fieldName) {
            if (str_starts_with($paramKey, 'og_') && str_ends_with($paramKey, '_field')) {
                $ogTag            = substr($paramKey, 0, -6);        // strip "_field"
                $mappings[$ogTag] = $fieldName;
            }
        }


        if (!$mappings) {
            return;                     // category has no mappings
        }

        $maxTitleLen                     = $this->params->get('max_title_length', 60);
        $maxDescLen                      = $this->params->get('max_description_length', 160);
        $maxAltLen                       = $this->params->get('max_alt_length', 125);
        $mappings["maxTitleLen"]         = $maxTitleLen;
        $mappings["maxDescLen"]          = $maxDescLen;
        $mappings["maxAltLen"]           = $maxAltLen;
        $mappings['twitter_title']       = $mappings['og_title'] ?? '';
        $mappings['twitter_description'] = $mappings['og_description'] ?? '';

        $document = $app->getDocument();

        $document->addScriptOptions('plgOgMappings', $mappings);

        Text::script('PLG_SYSTEM_OPENGRAPH_INHERITED');

        /** @var WebAssetManager $wa */
        $wa  = $document->getWebAssetManager();

        $wa->getRegistry()->addExtensionRegistryFile('plg_system_opengraph');
        $wa->useScript('plg_system_opengraph.opengraph-placeholder');
    }


    /**
     * Handle the beforeCompileHead event.
     *
     * @param BeforeCompileHeadEvent $event
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $app = $this->app;


        if (!$app->isClient('site')) {
            return;
        }

        /** @var HtmlDocument $document */
        $document = $app->getDocument();

        // Only process HTML documents
        if (!($document instanceof HtmlDocument)) {
            return;
        }

        $input  = $app->input;
        $view   = $input->getCmd('view');
        $id     = $input->getInt('id');
        $option = $input->getCmd('option');

        if ($option !== 'com_content') {
            return;
        }

        // Plugin disabled?
        if (!$this->params->get('enable_og_generation', 1)) {
            return;
        }

        $ogTags = $this->initializeOgTags();

        if ($view === 'article' && $id > 0) {
            $this->handleSingleArticle($document, $ogTags, $id, $option, $view);
            return;
        }
        $this->handleMultipleArticleView($document, $ogTags, $option, $view, $id);
    }




    /**
     * Handle Single Article
     * Priority: Category Mappings → Article Form → Single Article Menu (if available)
     *
     * @param HtmlDocument $document
     * @param array $ogTags
     * @param int $id
     * @param string $option
     * @param string $view
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function handleSingleArticle(HtmlDocument $document, array $ogTags, int $id, string $option, string $view): void
    {
        /** @var MVCComponent $component */
        $component = $this->app->bootComponent('com_content');

        /** @var MVCFactoryInterface $mvcFactory */
        $mvcFactory = $component->getMVCFactory();

        $params = ComponentHelper::getParams('com_content');
        if (!$params instanceof Registry) {
            $params = new Registry();
        }

        /** @var ArticleModel $articleModel */
        $articleModel = $mvcFactory->createModel('Article', 'Site', ['ignore_request' => true]);

        $articleModel->setState('params', clone $params);
        $articleModel->setState('article.id', $id);

        $article = $articleModel->getItem($id);
        if (!$article) {
            return;
        }

        /** @var CategoryModel $categoryModel */
        $categoryModel = $mvcFactory->createModel('Category', 'Site', ['ignore_request' => true]);
        $categoryModel->setState('category.id', $article->catid);
        $category = $categoryModel->getCategory();

        $articleAttribs = new Registry($article->attribs ?? '{}');
        $categoryParams = new Registry($category->params ?? '{}');
        $articleImages  = $this->getAllArticleImages(new Registry($article->images ?? '{}'));

        // Set article-specific properties
        $ogTags['og_type'] = 'article';

        // Step 1: Get OG tags from category mappings (Priority 1)
        $this->getOgTagsFromCategoryMappings($categoryParams, $article, $articleImages, $ogTags);

        // Step 2: Get OG tags from article form (Priority 2)
        $this->getOgTagsFromParams($articleAttribs, $ogTags);

        // Step 3: Check if there's a single article menu item and override (Priority 3 - Highest)
        $singleArticleMenuParams = $this->getSingleArticleMenuParams($option, $view, $id);
        $this->getOgTagsFromParams($singleArticleMenuParams, $ogTags);

        // Get default OG tags for any missing values
        $this->getDefaultOgTags($ogTags);

        // Get Twitter tags
        $this->getTwitterOgTags($ogTags);

        // Sanitize OG tags
        $this->sanitizeOgTags($ogTags);

        // Inject the OpenGraph data into the document
        $this->injectOpenGraphData($document, $ogTags);
    }

    /**
     * Handle Multiple Article Views (category, blog)
     * Only check menu form
     *
     * @param HtmlDocument $document
     * @param array $ogTags
     * @param string $option
     * @param string $view
     * @param int|null $categoryId
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function handleMultipleArticleView(HtmlDocument $document, array $ogTags, string $option, string $view, int|null $categoryId): void
    {


        $menuParams = $this->getMultipleArticleMenuParams($option, $view, $categoryId);


        if (!$menuParams->count()) {
            return;
        }

        // Apply menu parameters only
        $this->getOgTagsFromParams($menuParams, $ogTags);

        // Get Twitter tags
        $this->getTwitterOgTags($ogTags);

        // Sanitize OG tags
        $this->sanitizeOgTags($ogTags);

        // Inject the OpenGraph data into the document
        $this->injectOpenGraphData($document, $ogTags);
    }

    /**
     * Get menu parameters only for single article menu items
     *
     * @param string $option
     * @param string $view
     * @param int $id
     *
     * @return Registry
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getSingleArticleMenuParams(string $option, string $view, int $id): Registry
    {
        $menu   = $this->app->getMenu();
        $active = $menu->getActive();

        // Default empty params
        $params = new Registry();

        if (!$active || !isset($active->query['option']) || $active->query['option'] !== $option) {
            return $params;
        }

        // Only return params if this is a direct single article menu item
        if (
            isset($active->query['view']) && $active->query['view'] === 'article' &&
            isset($active->query['id']) && (int) $active->query['id'] === $id
        ) {
            return $active->getParams();
        }

        return $params;
    }

    /**
     * Get menu parameters for multiple article views (category, blog, featured)
     *
     * @param string $option
     * @param string $view
     * @param int|null $categoryId
     *
     * @return Registry
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getMultipleArticleMenuParams(string $option, string $view, ?int $categoryId = null): Registry
    {
        $menu   = $this->app->getMenu();
        $active = $menu->getActive();

        // Default empty params
        $params = new Registry();

        if (!$active || !isset($active->query['option']) || $active->query['option'] !== $option) {
            return $params;
        }

        // Check for direct menu match based on view type
        if (isset($active->query['view']) && $active->query['view'] === $view) {
            // For category-based views with an ID parameter
            if (\in_array($view, ['category', 'categoryblog'], true) && isset($active->query['id'])) {
                if ($categoryId !== null && (int) $active->query['id'] === (int) $categoryId) {
                    return $active->getParams();
                }
            } elseif ($view === 'featured') {
                // For featured view (no category ID)
                return $active->getParams();
            } else {
                // For other multi-article views, just return menu params
                return $active->getParams();
            }
        }

        return $params;
    }


    /**
     * Initialize OG tags array
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    private function initializeOgTags(): array
    {
        $config = $this->app->getConfig();

        return [
            'og_title'            => '',
            'og_description'      => '',
            'og_image'            => '',
            'og_image_alt'        => '',
            'og_type'             => 'website',
            'og_url'              => Uri::getInstance()->toString(),
            'twitter_card'        => 'summary',
            'twitter_title'       => '',
            'twitter_description' => '',
            'twitter_image'       => '',
            'twitter_image_alt'   => '',
            'fb_app_id'           => $this->params->get('fb_app_id', ''),
            'site_name'           => $config->get('sitename'),
            'url'                 => Uri::getInstance()->toString(),
            'base_url'            => Uri::base(),
        ];
    }






    /**
     * Generates OG metadata values based on category field mapping and article data.
     *
     * @param Registry $categoryParams The category params containing OG field mappings.
     * @param object $article The article object.
     * @param array $articleImages The array of article images.
     * @param array $ogTags The array of OG tags.
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getOgTagsFromCategoryMappings(Registry $categoryParams, object $article, array $articleImages, array &$ogTags): void
    {

        foreach ($categoryParams as $key => $fieldName) {
            // Only process keys that start with 'og_'
            if (strpos($key, 'og_') === 0 && str_ends_with($key, '_field')) {
                $ogTagName = substr($key, 0, -6); // Remove "_field" from the end

                $value              = $this->getFieldValue($article, $fieldName, $articleImages);
                $ogTags[$ogTagName] = $value;
            }
        }
    }



    /**
     * Get value from article field or custom field
     *
     * @param Content $article
     * @param string $fieldName
     * @param array $articleImages
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getFieldValue(object $article, string $fieldName, array $articleImages): string
    {

        // Check if it's a custom field
        if (strpos($fieldName, 'field.') === 0) {
            $customFieldName = substr($fieldName, 6);
            // Load custom fields for the article
            $customFields = FieldsHelper::getFields('com_content.article', $article, true);

            foreach ($customFields as $field) {
                if ($field->name == $customFieldName) {
                    return $field->value ?? '';
                }
            }

            return '';
        }

        // Handle standard article fields

        $value = '';

        switch ($fieldName) {
            case 'title':
                $value = $article->title;
                break;

            case 'alias':
                $value = $article->alias;
                break;

            case 'articletext':
                $value = $article->introtext . $article->fulltext;
                break;

            case 'metadesc':
                $value = $article->metadesc;
                break;

            case 'metakey':
                $value = $article->metakey;
                break;

            case 'image_intro':
                $value = $articleImages['image_intro'];
                break;

            case 'image_fulltext':
                $value = $articleImages['image_fulltext'];
                break;

            case 'image_intro_alt':
                $value = $articleImages['image_intro_alt'];
                break;

            case 'image_fulltext_alt':
                $value = $articleImages['image_fulltext_alt'];
                break;

            case 'created_by_alias':
                $value = $article->created_by_alias;
                break;

            default:
                if (property_exists($article, $fieldName)) {
                    $value = $article->$fieldName;
                }
        }

        return (string) $value;
    }



    /**
     * @param   Registry  $articleImages
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getAllArticleImages(Registry $articleImages): array
    {
        $image_intro    = $image_intro_alt = '';
        $image_fulltext = $image_fulltext_alt = '';

        // Handle image_intro
        if ($articleImages->get('image_intro') > '') {
            // get part before # in image if # exists
            $image_intro = strpos($articleImages->get('image_intro'), '#') !== false
                ? substr($articleImages->get('image_intro'), 0, strpos($articleImages->get('image_intro'), '#'))
                : $articleImages->get('image_intro');

            $image_intro_alt = $articleImages->get('image_intro_alt', '');
        }

        // Handle image_fulltext
        if ($articleImages->get('image_fulltext') > '') {
            $image_fulltext = strpos($articleImages->get('image_fulltext'), '#') !== false
                ? substr($articleImages->get('image_fulltext'), 0, strpos($articleImages->get('image_fulltext'), '#'))
                : $articleImages->get('image_fulltext');

            $image_fulltext_alt = $articleImages->get('image_fulltext_alt', '');
        }

        return [
            'image_intro'        => $image_intro,
            'image_intro_alt'    => $image_intro_alt,
            'image_fulltext'     => $image_fulltext,
            'image_fulltext_alt' => $image_fulltext_alt,
        ];
    }



    /**
     * Extract OG tags from a parameter source (article, menu)
     *
     * @param Registry $params The source of OG field mappings (e.g. article attribs, menu params)
     * @param array &$ogTags Reference to the OG tags array to populate
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getOgTagsFromParams(Registry $params, array &$ogTags): void
    {

        foreach (array_keys($ogTags) as $ogTagName) {
            if ($params->exists($ogTagName)) {
                $value = $params->get($ogTagName);


                if ($value) {
                    $ogTags[$ogTagName] = $value;
                }
            }
        }
    }



    /**
     * Get Global Default OG tags if not till not set
     * @param array &$ogTags
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getDefaultOgTags(array &$ogTags): void
    {
        // Get Global Default OG tags if not set
        $defaultOgTags = [
            'og_title'       => $this->params->get('default_og_title'),
            'og_description' => $this->params->get('default_og_description'),
            'og_image'       => $this->params->get('default_og_image'),
            'og_image_alt'   => $this->params->get('default_og_image_alt'),
            'site_name'      => $this->params->get('default_og_site_name'),
            'fb_app_id'      => $this->params->get('fb_app_id'),
        ];

        foreach ($defaultOgTags as $key => $value) {
            if ($ogTags[$key] === '') {
                $ogTags[$key] = $value;
            }
        }
    }




    /**
     * Get Twitter tags if not set use OG value
     * @param array &$ogTags
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function getTwitterOgTags(array &$ogTags): void
    {
        $twitterTags = [
            'twitter_title'       => $ogTags['twitter_title'],
            'twitter_description' => $ogTags['twitter_description'],
            'twitter_image'       => $ogTags['twitter_image'],
            'twitter_image_alt'   => $ogTags['twitter_image_alt'],
        ];
        foreach ($twitterTags as $key => $value) {
            // If the value is not set, use the OG value
            if (!$value || $value === '') {
                $ogTags[$key] = $ogTags['og_' . substr($key, 8)];
            }
        }
    }

    /**
     * Inject the OpenGraph data into the document.
     *
     * @param Document $document
     * @param array $ogTags
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function injectOpenGraphData(Document $document, array $ogTags): void
    {
        // OpenGraph tags
        $this->setMetaData($document, 'og:title', $ogTags['og_title'], 'property');
        $this->setMetaData($document, 'og:description', $ogTags['og_description'], 'property');
        $this->setMetaData($document, 'og:type', $ogTags['og_type'], 'property');
        $this->setMetaData($document, 'og:url', $ogTags['og_url'], 'property');

        // Twitter tags
        $this->setMetaData($document, 'twitter:card', $ogTags['twitter_card'], 'name');
        $this->setMetaData($document, 'twitter:title', $ogTags['twitter_title'], 'name');
        $this->setMetaData($document, 'twitter:description', $ogTags['twitter_description'], 'name');

        // Facebook App ID
        $this->setMetaData($document, 'fb:app_id', $ogTags['fb_app_id'], 'property');

        $this->setMetaData($document, 'og:site_name', $ogTags['site_name'], 'property');

        $this->setOpenGraphImage($document, $ogTags);
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
     *
     * @since  __DEPLOY_VERSION__
     */
    private function setMetaData(Document $document, string $name, ?string $value, string $attributeType): void
    {
        if (!empty($value)) {
            $document->setMetaData($name, $value, $attributeType);
        }
    }

    /**
     * Add OpenGraph image to document
     *
     * @param   Document  $document
     * @param   array     $ogTags
     *
     * @return void
     * @since  __DEPLOY_VERSION__
     */
    private function setOpenGraphImage(
        Document $document,
        array $ogTags
    ): void {
        $image           = $ogTags['og_image'];
        $alt             = $ogTags['og_image_alt'];
        $baseUrl         = $ogTags['base_url'];
        $twitterImage    = $ogTags['twitter_image'];
        $twitterImageAlt = $ogTags['twitter_image_alt'];

        if (empty($image) || !empty($document->getMetaData('og:image'))) {
            return;
        }

        $image = preg_replace('~^([\w\-./\\\]+).*$~', '$1', $image);

        if (!file_exists($image)) {
            return;
        }

        $ogImageUrl = empty($baseUrl) ? '' : rtrim($baseUrl, '/') . '/';
        $ogImageUrl .= $image;

        $twitterImageUrl = empty($baseUrl) ? '' : rtrim($baseUrl, '/') . '/';
        $twitterImageUrl .= $twitterImage;

        $this->setMetaData($document, 'og:image', $ogImageUrl, 'property');
        $this->setMetaData($document, 'og:image:secure_url', $ogImageUrl, 'property');
        $this->setMetaData($document, 'og:image:alt', $alt, 'property');
        $this->setMetaData($document, 'twitter:image', $twitterImageUrl, 'name');
        $this->setMetaData($document, 'twitter:image:alt', $twitterImageAlt, 'name');

        $info = getimagesize($image);

        if (\is_array($info)) {
            $this->setMetaData($document, 'og:image:type', $info['mime'], 'property');
            $this->setMetaData($document, 'og:image:height', $info[1], 'property');
            $this->setMetaData($document, 'og:image:width', $info[0], 'property');
        }
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
        } catch (\Exception $e) {
            error_log('OpenGraph Plugin: Failed to boot component: ' . $e->getMessage());
            return false;
        }

        return $component instanceof OpengraphServiceInterface;
    }


    /**
     * Adjust the fields group in the XML file
     *
     * @param string $filePath
     * @param string $newGroup
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     */
    private function adjustFieldsGroup(string $filePath, string $newGroup): string
    {
        $xmlContent = file_get_contents($filePath);
        $xml        = simplexml_load_string($xmlContent);

        if ($xml === false) {
            throw new \Exception("Could not load XML file: {$filePath}");
        }

        // Adjust all <fields> nodes to use the desired group
        foreach ($xml->xpath('//fields') as $fields) {
            $fields['name'] = $newGroup;
        }

        return $xml->asXML();
    }


    /**
     * Clean up and normalise all OG / Twitter tag values.
     *
     * @param  array  &$ogTags  Reference to the tag array created earlier.
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function sanitizeOgTags(array &$ogTags): void
    {
        // TODO : will be configurable in the future from the plugin settings

        $maxTitleLen   = $this->params->get('max_title_length', 60); // Facebook shows ~55–60 chars, Twitter ~70
        $maxDescLen    = $this->params->get('max_description_length', 160); // Twitter summary cards truncate after ~160
        $maxAltLen     = $this->params->get('max_alt_length', 125); // WCAG recommendation for alt text

        foreach (['og_title', 'twitter_title'] as $key) {
            $ogTags[$key] = $this->cleanText($ogTags[$key] ?? '', $maxTitleLen);
        }

        foreach (['og_description', 'twitter_description'] as $key) {
            $ogTags[$key] = $this->cleanText($ogTags[$key] ?? '', $maxDescLen);
        }

        foreach (['og_image_alt', 'twitter_image_alt'] as $key) {
            $ogTags[$key] = $this->cleanText($ogTags[$key] ?? '', $maxAltLen);
        }

        // Make sure og:url is absolute
        if (!empty($ogTags['og_url']) && !preg_match('~^https?://~i', $ogTags['og_url'])) {
            $ogTags['og_url'] = Uri::root() . ltrim($ogTags['og_url'], '/');
        }
    }


    /**
     * Helper: strip HTML, decode entities, collapse whitespace, then truncate on a word boundary and add an ellipsis if needed.
     *
     * @param string $text
     * @param int $maxLen
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     */
    private function cleanText(string $text, int $maxLen): string
    {
        // Remove tags and entities, normalise whitespace
        $plain = OutputFilter::cleanText($text);

        // Truncate the text on a word boundary and add an ellipsis if needed
        $truncated = HTMLHelper::_('string.truncate', $plain, $maxLen, true, false);

        // Replace the three-dot ellipsis with a single Unicode one
        return preg_replace('/\.\.\.$/', '…', $truncated);
    }
}
