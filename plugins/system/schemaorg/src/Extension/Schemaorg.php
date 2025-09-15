<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.schemaorg
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Schemaorg\Extension;

use Joomla\CMS\Event\Application\BeforeCompileHeadEvent as BeforeCompileHeadApplicationEvent;
use Joomla\CMS\Event\Model;
use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareDataEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareFormEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareSaveEvent;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareImageTrait;
use Joomla\CMS\Schemaorg\SchemaorgServiceInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Schemaorg System Plugin
 *
 * @since  5.0.0
 */
final class Schemaorg extends CMSPlugin implements SubscriberInterface, DispatcherAwareInterface
{
    use DatabaseAwareTrait;
    use DispatcherAwareTrait;
    use SchemaorgPrepareDateTrait;
    use SchemaorgPrepareImageTrait;
    use UserFactoryAwareTrait;

    /**
     * Map of schema types to their role fields
     *
     * @var array
     *
     * @since __DEPLOY_VERSION__
     */
    private const ROLE_CONTACT_MAP = [
        'Article' => [
            'author',
        ],
        'BlogPosting' => [
            'author',
        ],
        'Book' => [
            'illustrator',
        ],
        'Event' => [
            'organizer',
        ],

    ];

    /**
     * Temporarily holds schema data between onContentPrepareData and onContentPrepareForm events.
     *
     * @var  ?array
     * @since __DEPLOY_VERSION__
     */
    private ?array $preparedSchemaData = null;
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead'  => 'onBeforeCompileHead',
            'onContentPrepareData' => 'onContentPrepareData',
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onContentAfterSave'   => 'onContentAfterSave',
            'onContentAfterDelete' => 'onContentAfterDelete',
        ];
    }

    /**
     * Runs on content preparation
     *
     * @param   Model\PrepareDataEvent  $event  The event
     *
     * @since   5.0.0
     *
     */
    public function onContentPrepareData(Model\PrepareDataEvent $event)
    {
        $context = $event->getContext();
        $data    = $event->getData();

        $app = $this->getApplication();

        if ($app->isClient('site') || !$this->isSupported($context)) {
            return;
        }

        $data = (object) $data;

        $itemId = $data->id ?? 0;

        // Check if the form already has some data
        if ($itemId > 0) {
            $db = $this->getDatabase();

            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . '= :itemId')
                ->bind(':itemId', $itemId, ParameterType::INTEGER)
                ->where($db->quoteName('context') . '= :context')
                ->bind(':context', $context, ParameterType::STRING);

            $results = $db->setQuery($query)->loadAssoc();

            if (empty($results)) {
                return;
            }

            $schemaType                 = $results['schemaType'];
            $data->schema['schemaType'] = $schemaType;

            $schema = new Registry($results['schema']);

            $data->schema[$schemaType] = $schema->toArray();
            // Store the loaded data for use in onContentPrepareForm
            $this->preparedSchemaData = $data->schema;
        }

        $dispatcher = $this->getDispatcher();
        $event      = new PrepareDataEvent('onSchemaPrepareData', [
            'subject' => $data,
            'context' => $context,
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaPrepareData', $event);
    }

    /**
     * The form event.
     *
     * @param   Model\PrepareFormEvent  $event  The event
     *
     * @since   5.0.0
     */
    public function onContentPrepareForm(Model\PrepareFormEvent $event)
    {
        $form    = $event->getForm();
        $context = $form->getName();
        $app     = $this->getApplication();

        if (!$app->isClient('administrator') || !$this->isSupported($context)) {
            return;
        }

        // Load plugin language files.
        $this->loadLanguage();

        // Load the form fields
        $form->loadFile(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/schemaorg.xml');

        // The user should configure the plugin first
        if (!$this->params->get('baseType')) {
            $form->removeField('schemaType', 'schema');

            $plugin = PluginHelper::getPlugin('system', 'schemaorg');

            $user = $this->getApplication()->getIdentity();

            $infoText = Text::_('PLG_SYSTEM_SCHEMAORG_FIELD_SCHEMA_DESCRIPTION_NOT_CONFIGURED');

            // If edit permission are available, offer a link
            if ($user->authorise('core.edit', 'com_plugins')) {
                $infoText = Text::sprintf('PLG_SYSTEM_SCHEMAORG_FIELD_SCHEMA_DESCRIPTION_NOT_CONFIGURED_ADMIN', (int) $plugin->id);
            }

            $form->setFieldAttribute('schemainfo', 'description', $infoText, 'schema');

            $form->setFieldAttribute('extendJed', 'type', 'hidden', 'schema');
            $form->setFieldAttribute('extendJed', 'class', 'hidden', 'schema');

            return;
        }

        $dispatcher = $this->getDispatcher();
        $event      = new PrepareFormEvent('onSchemaPrepareForm', [
            'subject' => $form,
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaPrepareForm', $event);

        // Inject contact fields into relevant roles
        foreach (self::ROLE_CONTACT_MAP as $type => $roles) {
            foreach ($roles as $role) {
                $this->injectContactField($form, $type, $role);
            }
        }

        // After injecting contact fields, load the JavaScript
        if ($app->isClient('administrator') && $this->isSupported($context)) {
            $contactId = 0;
            if ($this->preparedSchemaData) {
                foreach (self::ROLE_CONTACT_MAP as $type => $roles) {
                    if (isset($this->preparedSchemaData[$type])) {
                        foreach ($roles as $role) {
                            if (!empty($this->preparedSchemaData[$type][$role]['contact'])) {
                                $contactId = (int) $this->preparedSchemaData[$type][$role]['contact'];
                                break 2;
                            }
                        }
                    }
                }
            }

            $this->loadContactFieldAssets($contactId);
        }
    }

    /**
     * Load JavaScript and CSS assets for contact field functionality
     * and pass pre-loaded contact data if it exists.
     *
     * @param   int  $contactId  The ID of a pre-selected contact, or 0 if none.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadContactFieldAssets(int $contactId = 0): void
    {

        $app = $this->getApplication();
        $doc = $app->getDocument();

        if (!($doc instanceof \Joomla\CMS\Document\HtmlDocument)) {
            return;
        }
        $defaultContactId = (int) $this->params->get('defaultContact', 0);
        $isDefaultContact = false;
        if ($contactId <= 0 && $defaultContactId > 0) {
            $contactId        = $defaultContactId;
            $isDefaultContact = true;
        }
        $initialContactData = null;
        if ($contactId > 0) {

            /** @var \Joomla\CMS\Extension\MVCComponent $component */
            $component  = $app->bootComponent('com_contact');
            $mvcFactory = $component->getMVCFactory();

            /** @var ContactModel $contactModel */
            $contactModel = $mvcFactory->createModel('Contact', 'Administrator', ['ignore_request' => true]);
            $contact      = $contactModel->getItem($contactId);
            if ($contact) {
                $initialContactData = [
                    'id'               => (int) ($contact->id ?? 0),
                    'name'             => $contact->name ?? '',
                    'email_to'         => $contact->email_to ?? '',
                    'address'          => $contact->address ?? '',
                    'street'           => $contact->street ?? '',
                    'suburb'           => $contact->suburb ?? '',
                    'state'            => $contact->state ?? '',
                    'postcode'         => $contact->postcode ?? '',
                    'country'          => $contact->country ?? '',
                    'telephone'        => $contact->telephone ?? '',
                    'webpage'          => $contact->webpage ?? '',
                    'isDefaultContact' => $isDefaultContact,
                ];
            }
        }
        $wa = $doc->getWebAssetManager();

        // Register the asset if not already registered
        if (!$wa->assetExists('script', 'plg_system_schemaorg.contact')) {
            $wa->registerScript(
                'plg_system_schemaorg.contact',
                'plg_system_schemaorg/schemaorg-contact.js',
                ['version' => 'auto', 'relative' => true],
                ['defer'   => true],
                ['core']
            );
        }

        // // Use the assets
        $wa->useScript('plg_system_schemaorg.contact');

        // Add inline configuration
        $doc->addScriptOptions('plg_system_schemaorg', [
            'initialContact' => $initialContactData,
        ]);
    }

    /**
     * Inject a Contact selector field into a role subform for a given Schema.org type.
     *
     * @param  \Joomla\CMS\Form\Form  $form  The active form being prepared.
     * @param  string                 $type  Schema.org type name (e.g. "Article", "Book").
     * @param  string                 $role  Role field name under that type (e.g. "author", "illustrator").
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    private function injectContactField(\Joomla\CMS\Form\Form $form, string $type, string $role): void
    {

        $xml   = $form->getXml();
        $nodes = $xml->xpath("//fieldset[@name='schema']/field[@name='{$type}']/form/field[@name='{$role}']");
        if (!$nodes || !isset($nodes[0])) {
            return; // no such role in this type
        }

        $roleField = $nodes[0];

        // Get current user and permission checks for com_contact
        $user      = $this->getApplication()->getIdentity();
        $canCreate = $user->authorise('core.create', 'com_contact');
        $canEdit   = $user->authorise('core.edit', 'com_contact');
        $canView   = $user->authorise('core.view', 'com_contact');

        $contact = new \SimpleXMLElement('<field/>');
        $contact->addAttribute('name', 'contact');
        $contact->addAttribute('type', 'modal_contact');
        $contact->addAttribute('label', 'COM_CONTACT_SELECT_CONTACT_LABEL');
        $contact->addAttribute('hiddenLabel', 'true');

        // Only show select if user can view contacts
        if ($canView) {
            $contact->addAttribute('select', 'true');
        }

        // Only allow creating a contact if user has create permission
        if ($canCreate) {
            $contact->addAttribute('new', 'true');
        }

        // Edit button shown only to users with edit permission
        if ($canEdit) {
            $contact->addAttribute('edit', 'true');
        }

        // Clear should be allowed if user can view (so they can clear their selection)
        if ($canView) {
            $contact->addAttribute('clear', 'true');
        }

        $contact->addAttribute('addfieldprefix', 'Joomla\Component\Contact\Administrator\Field');

        // Prepend into the role’s inner <form> so it’s the first control
        $domForm    = dom_import_simplexml($roleField->form);
        $domContact = $domForm->ownerDocument->importNode(dom_import_simplexml($contact), true);
        $domForm->insertBefore($domContact, $domForm->firstChild);
    }

    /**
     * Enrich top-level @graph entries for configured types/roles using com_contact data.
     *
     * @param array $graph
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    private function enrichGraphContacts(array &$graph): void
    {


        foreach ($graph as &$entry) {
            if (!\is_array($entry) || empty($entry['@type'])) {
                continue;
            }

            $type = $entry['@type'];

            if (!isset(self::ROLE_CONTACT_MAP[$type])) {
                continue;
            }

            foreach (self::ROLE_CONTACT_MAP[$type] as $role) {
                if (!isset($entry[$role])) {
                    continue;
                }

                // Normalize to list of role nodes by reference
                $roleNodes = [];
                $roleNodes = [&$entry[$role]];


                // Enrich each role node when it has a contact id
                $filledNodes = 0;
                foreach ($roleNodes as &$roleNode) {
                    if (!isset($roleNode['contact']) || (int) $roleNode['contact'] <= 0) {
                        continue;
                    }
                    $contact = $this->getContactById((int) $roleNode['contact']);
                    if ($contact) {
                        $this->fillNodeFromContact($roleNode, $contact);
                        $filledNodes++;
                        unset($roleNode['contact']);
                    }
                }

                if ($filledNodes == 0) {
                    $defaultContactId = (int) $this->params->get('defaultContact', 0);
                    if ($defaultContactId > 0) {
                        $contact = $this->getContactById($defaultContactId);
                        if ($contact) {
                            $this->fillNodeFromContact($roleNode, $contact);
                        }
                    }
                }
            }
        }
    }



    /**
     * Fetch a com_contact record by its ID.
     *
     * @param int $id
     *
     * @return stdClass|null
     *
     * @since __DEPLOY_VERSION__
     */
    private function getContactById(int $id)
    {
        try {
            $app = $this->getApplication();

            /** @var \Joomla\CMS\Extension\MVCComponent $component */
            $component  = $app->bootComponent('com_contact');
            $mvcFactory = $component->getMVCFactory();

            /** @var \Joomla\Component\Contact\Site\Model\ContactModel $model */
            $model = $mvcFactory->createModel('Contact', 'Site', ['ignore_request' => true]);

            // Set state as com_contact does in front-end
            $model->setState('params', $app->getParams());
            $model->setState('contact.id', $id);

            $contact = $model->getItem();

            // Basic sanity check
            if (!empty($contact) && (int) ($contact->id ?? 0) === $id) {
                return $contact;
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    /**
     * Copy contact details into the role node.
     *
     * @param array    $node     Role node array (passed by reference).
     * @param stdClass $contact  com_contact record.
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    private function fillNodeFromContact(array &$node, $contact): void
    {
        // Core fields (fill only if missing)
        if (empty($node['name']) && !empty($contact->name)) {
            $node['name'] = $contact->name;
        }
        if (empty($node['email']) && !empty($contact->email_to)) {
            $node['email'] = $contact->email_to;
        }
        if (empty($node['url']) && !empty($contact->webpage)) {
            $node['url'] = $contact->webpage;
        }

        // Build a PostalAddress object as "address"

        if (isset($node['address']) && \is_array($node['address'])) {
            $addr = $node['address'];
        } else {
            $addr = [];
        }

        $addr['@type'] = 'PostalAddress';


        // Only set if missing to avoid overriding manually filled values
        if (empty($addr['addressLocality'])) {
            if (!empty($node['addressLocality'])) {
                $addr['addressLocality'] = $node['addressLocality'];
            } elseif (!empty($contact->suburb)) {
                $addr['addressLocality'] = $contact->suburb;
            }
        }

        if (empty($addr['postalCode'])) {
            if (!empty($node['postalCode'])) {
                $addr['postalCode'] = $node['postalCode'];
            } elseif (!empty($contact->postcode)) {
                $addr['postalCode'] = $contact->postcode;
            }
        }

        if (empty($addr['streetAddress'])) {
            if (!empty($node['streetAddress'])) {
                $addr['streetAddress'] = $node['streetAddress'];
            } elseif (!empty($contact->street)) {
                $addr['streetAddress'] = $contact->street;
            } elseif (!empty($contact->address)) {
                // As a fallback, use the flat address if street isn’t split out
                $addr['streetAddress'] = $contact->address;
            }
        }

        if (empty($addr['addressRegion'])) {
            if (!empty($node['addressRegion'])) {
                $addr['addressRegion'] = $node['addressRegion'];
            } elseif (!empty($contact->state)) {
                $addr['addressRegion'] = $contact->state;
            }
        }

        if (empty($addr['addressCountry'])) {
            if (!empty($node['addressCountry'])) {
                $addr['addressCountry'] = $node['addressCountry'];
            } elseif (!empty($contact->country)) {
                $addr['addressCountry'] = $contact->country;
            }
        }

        $node['address'] = $addr;
    }


    /**
     * Saves form field data in the database
     *
     * @param   Model\AfterSaveEvent $event
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onContentAfterSave(Model\AfterSaveEvent $event)
    {
        $context = $event->getContext();
        $table   = $event->getItem();
        $isNew   = $event->getIsNew();
        $data    = $event->getData();
        $app     = $this->getApplication();
        $db      = $this->getDatabase();

        if (!$app->isClient('administrator') || !$this->isSupported($context)) {
            return;
        }

        $itemId = (int) $table->id;

        if (empty($data['schema']) || empty($data['schema']['schemaType']) || $data['schema']['schemaType'] === 'None') {
            $this->deleteSchemaOrg($itemId, $context);
            return;
        }

        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . '= :itemId')
            ->bind(':itemId', $itemId, ParameterType::INTEGER)
            ->where($db->quoteName('context') . '= :context')
            ->bind(':context', $context, ParameterType::STRING);

        $entry = $db->setQuery($query)->loadObject();

        if (empty($entry->id)) {
            $entry = new \stdClass();
        }

        $entry->itemId     = (int) $table->getId();
        $entry->context    = $context;

        if (isset($data['schema']['schemaType'])) {
            $entry->schemaType = $data['schema']['schemaType'];

            if (isset($data['schema'][$entry->schemaType])) {
                $entry->schema = (new Registry($data['schema'][$entry->schemaType]))->toString();
            }
        }

        $dispatcher = $this->getDispatcher();
        $event      = new PrepareSaveEvent('onSchemaPrepareSave', [
            'subject' => $entry,
            'context' => $context,
            'item'    => $table,
            'isNew'   => $isNew,
            'schema'  => $data['schema'],
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaPrepareSave', $event);

        if (!isset($entry->schemaType)) {
            return;
        }

        if (!empty($entry->id)) {
            $db->updateObject('#__schemaorg', $entry, 'id');
        } else {
            $db->insertObject('#__schemaorg', $entry, 'id');
        }
    }

    /**
     * This event is triggered before the framework creates the Head section of the Document
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onBeforeCompileHead(BeforeCompileHeadApplicationEvent $event): void
    {
        $app      = $event->getApplication();
        $doc      = $event->getDocument();
        $wa       = $doc->getWebAssetManager();
        $baseType = $this->params->get('baseType', 'organization');

        $itemId  = (int) $app->getInput()->getInt('id');
        $option  = $app->getInput()->get('option');
        $view    = $app->getInput()->get('view');
        $context = $option . '.' . $view;

        // We need the plugin configured at least once to add structured data
        if (!$app->isClient('site') || !\in_array($baseType, ['organization', 'person']) || !$this->isSupported($context)) {
            return;
        }

        $domain = Uri::root();

        $isPerson = $baseType === 'person';

        $schema = new Registry();

        $baseSchema = [];

        $baseSchema['@context'] = 'https://schema.org';
        $baseSchema['@graph']   = [];

        // Add base tag Person/Organization
        $baseId = $domain . '#/schema/' . ucfirst($baseType) . '/base';

        $siteSchema = [];

        $siteSchema['@type'] = ucfirst($baseType);
        $siteSchema['@id']   = $baseId;

        $name = $this->params->get('name', $app->get('sitename'));

        if ($isPerson && $this->params->get('user') > 0) {
            $user = $this->getUserFactory()->loadUserById($this->params->get('user'));

            $name = $user ? $user->name : '';
        }

        if ($name) {
            $siteSchema['name'] = $name;
        }

        $siteSchema['url'] = $domain;

        // Image
        $image = $this->params->get('image') ? HTMLHelper::_('cleanimageUrl', $this->params->get('image')) : false;

        if ($image !== false) {
            $siteSchema['logo'] = [
                '@type'      => 'ImageObject',
                '@id'        => $domain . '#/schema/ImageObject/logo',
                'url'        => $image->url,
                'contentUrl' => $image->url,
                'width'      => $image->attributes['width'] ?? 0,
                'height'     => $image->attributes['height'] ?? 0,
            ];

            $siteSchema['image'] = ['@id' => $siteSchema['logo']['@id']];
        }

        // Social media accounts
        $socialMedia = (array) $this->params->get('socialmedia', []);

        if (!empty($socialMedia)) {
            $siteSchema['sameAs'] = [];
        }

        foreach ($socialMedia as $social) {
            $siteSchema['sameAs'][] = $social->url;
        }

        $baseSchema['@graph'][] = $siteSchema;

        // Add WebSite
        $webSiteId = $domain . '#/schema/WebSite/base';

        $webSiteSchema = [];

        $webSiteSchema['@type']      = 'WebSite';
        $webSiteSchema['@id']        = $webSiteId;
        $webSiteSchema['url']        = $domain;
        $webSiteSchema['name']       = $app->get('sitename');
        $webSiteSchema['publisher']  = ['@id' => $baseId];

        // We support Finder actions
        $finder = ModuleHelper::getModule('mod_finder');

        if (!empty($finder->id)) {
            $webSiteSchema['potentialAction'] = [
                '@type'       => 'SearchAction',
                'target'      => Route::_('index.php?option=com_finder&view=search&q={search_term_string}', true, Route::TLS_IGNORE, true),
                'query-input' => 'required name=search_term_string',
            ];
        }

        $baseSchema['@graph'][] = $webSiteSchema;

        // Add WebPage
        $webPageId = $domain . '#/schema/WebPage/base';

        $webPageSchema = [];

        $webPageSchema['@type']       = 'WebPage';
        $webPageSchema['@id']         = $webPageId;
        $webPageSchema['url']         = htmlspecialchars(Uri::getInstance()->toString());
        $webPageSchema['name']        = $app->getDocument()->getTitle();
        $webPageSchema['description'] = $app->getDocument()->getDescription();
        $webPageSchema['isPartOf']    = ['@id' => $webSiteId];
        $webPageSchema['about']       = ['@id' => $baseId];
        $webPageSchema['inLanguage']  = $app->getLanguage()->getTag();

        // Support Breadcrumb Schema linking
        try {
            try {
                $breadcrumbsAsset = $wa->getRegistry()->get('script', 'inline.breadcrumbs-schemaorg');
            } catch (UnknownAssetException $e) {
                // Fallback for older versions of the breadcrumbs module
                $breadcrumbsAsset = $wa->getRegistry()->get('script', 'inline.mod_breadcrumbs-schemaorg');
                trigger_deprecation(
                    'joomla/schemaorg',
                    '5.4',
                    'The inline.mod_breadcrumbs-schemaorg asset name is deprecated. Please use the generic inline.breadcrumbs-schemaorg asset name instead.'
                );
            }

            $breadcrumbs = json_decode($breadcrumbsAsset->getOption('content'), true, 512, JSON_THROW_ON_ERROR);

            if ($breadcrumbs['@type'] !== 'BreadcrumbList') {
                trigger_error('The breadcrumbs schema is not of type BreadcrumbList', E_USER_WARNING);
                throw new UnknownAssetException();
            }

            $webPageSchema['breadcrumb'] = ['@id' => $breadcrumbs['@id']];
        } catch (UnknownAssetException $e) {
            // No Breadcrumbs Schema found, so we don't add it
        }

        $baseSchema['@graph'][] = $webPageSchema;

        if ($itemId > 0) {
            // Load the table data from the database
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . ' = :itemId')
                ->bind(':itemId', $itemId, ParameterType::INTEGER)
                ->where($db->quoteName('context') . ' = :context')
                ->bind(':context', $context, ParameterType::STRING);

            $result = $db->setQuery($query)->loadObject();

            if ($result) {
                $localSchema = new Registry($result->schema);

                $localSchema->set('@id', $domain . '#/schema/' . str_replace('.', '/', $context) . '/' . (int) $result->itemId);
                $localSchema->set('isPartOf', ['@id' => $webPageId]);

                $itemSchema = $localSchema->toArray();

                $baseSchema['@graph'][] = $itemSchema;
            }
        }

        $schema->loadArray($baseSchema);

        $dispatcher = $this->getDispatcher();
        $event      = new BeforeCompileHeadEvent('onSchemaBeforeCompileHead', [
            'subject' => $schema,
            'context' => $context . '.' . $itemId,
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaBeforeCompileHead', $event);

        $data = $schema->get('@graph') ?: [];

        // Enrich contacts from com_contact
        $this->enrichGraphContacts($data);

        foreach ($data as $key => $entry) {
            $data[$key] = $this->cleanupSchema($entry);
        }

        $schema->set('@graph', $data);

        $prettyPrint  = JDEBUG ? JSON_PRETTY_PRINT : 0;
        $schemaString = $schema->toString('JSON', ['bitmask' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | $prettyPrint]);

        if ($schemaString !== '{}') {
            $wa->addInlineScript($schemaString, ['name' => 'inline.schemaorg'], ['type' => 'application/ld+json']);
        }
    }

    /**
     * Clean the schema and remove empty fields
     *
     * @param   array  $schema
     *
     * @return  array
     *
     * @since  5.0.0
     */
    private function cleanupSchema($schema)
    {
        $result = [];

        foreach ($schema as $key => $value) {
            if (\is_array($value)) {
                // Subtypes need special handling
                if (!empty($value['@type'])) {
                    if ($value['@type'] === 'ImageObject') {
                        if (!empty($value['url'])) {
                            $value['url'] = $this->prepareImage($value['url']);
                        }

                        if (empty($value['url'])) {
                            $value = [];
                        }
                    } elseif ($value['@type'] === 'Date') {
                        if (!empty($value['value'])) {
                            $value['value'] = $this->prepareDate($value['value']);
                        }

                        if (empty($value['value'])) {
                            $value = [];
                        }
                    }

                    // Go into the array
                    $value = $this->cleanupSchema($value);

                    // We don't save when the array contains only the @type
                    if (empty($value) || \count($value) <= 1) {
                        $value = null;
                    }
                } elseif ($key == 'genericField') {
                    foreach ($value as $field) {
                        $result[$field['genericTitle']] = $field['genericValue'];
                    }

                    continue;
                }
            }

            // No data, no play
            if (empty($value)) {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Check if the current plugin should execute schemaorg related activities
     *
     * @param   string  $context
     *
     * @return   boolean
     *
     * @since   5.0.0
     */
    protected function isSupported($context)
    {
        // We need at least the extension + view for loading the table fields
        if (!str_contains($context, '.')) {
            return false;
        }

        $parts     = explode('.', $context, 2);
        $component = $this->getApplication()->bootComponent($parts[0]);

        if ($component instanceof SchemaorgServiceInterface) {
            return \in_array($context, array_keys($component->getSchemaorgContexts()));
        }

        return false;
    }

    /**
     * The delete event.
     *
     * @param   Object    $event  The event
     *
     * @return  void
     *
     * @since   5.1.3
     */
    public function onContentAfterDelete(Model\AfterDeleteEvent $event)
    {
        $context = $event->getContext();
        $itemId  = $event->getItem()->id ?? 0;

        if (!$itemId || !$this->isSupported($context)) {
            return;
        }

        $this->deleteSchemaOrg($itemId, $context);
    }

    /**
     * Delete SchemaOrg record from Database.
     *
     * @param   Integer   $itemId
     * @param   String    $context
     *
     * @return  void
     *
     * @since   5.1.3
     */
    public function deleteSchemaOrg($itemId, $context)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . '= :itemId')
            ->where($db->quoteName('context') . '= :context')
            ->bind(':itemId', $itemId, ParameterType::INTEGER)
            ->bind(':context', $context, ParameterType::STRING);

        $db->setQuery($query)->execute();
    }
}
