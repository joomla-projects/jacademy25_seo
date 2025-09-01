<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.article
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Article\Extension;

use Joomla\CMS\Document\Factory;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareImageTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Schemaorg Plugin
 *
 * @since  5.1.0
 */
final class Article extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPluginTrait;
    use SchemaorgPrepareDateTrait;
    use SchemaorgPrepareImageTrait;


    /**
     * The application object.
     *
     * @var CMSApplication
     */
    protected $app;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  5.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * The name of the schema form
     *
     * @var   string
     * @since 5.1.0
     */
    protected $pluginName = 'Article';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSchemaPrepareForm'       => 'onSchemaPrepareForm',
            'onSchemaBeforeCompileHead' => ['onSchemaBeforeCompileHead', Priority::BELOW_NORMAL],
        ];
    }

    /**
     * Cleanup all Article types
     *
     * @param   BeforeCompileHeadEvent  $event  The given event
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function onSchemaBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $schema = $event->getSchema();

        $graph = $schema->get('@graph');

        $app = $this->app;
        $contactId = null;

        foreach ($graph as $entry) {
            // Loop through each property inside the entry
            foreach ($entry as $key => $value) {
                // If the property itself is an array (like author, organiser etc.)
                if (is_array($value)) {
                    if (isset($value['contact'])) {
                        $contactId = $value['contact'];
                        // You can break out here if you only need the first contact
                        break 2;
                    }
                }
            }
        }

        $contact = null;
        if ($contactId) {
            /** @var MVCComponent $component */
            $component = $app->bootComponent('com_contact');
            /** @var MVCFactoryInterface $mvcFactory */
            $mvcFactory = $component->getMVCFactory();
            /** @var ContactModel $model */
            $contactModel = $mvcFactory->createModel('Contact', 'Site', ['ignore_request' => true]);

            $contactModel->setState('params', \Joomla\CMS\Factory::getApplication()->getParams());
            $contactModel->setState('contact.id', 1);


            $contact = $contactModel->getItem();
        }

        foreach ($graph as &$entry) {

            if (!isset($entry['@type']) || $entry['@type'] !== 'Article') {
                continue;
            }

            if (!empty($entry['datePublished'])) {
                $entry['datePublished'] = $this->prepareDate($entry['datePublished']);
            }

            if (!empty($entry['dateModified'])) {
                $entry['dateModified'] = $this->prepareDate($entry['dateModified']);
            }

            if (!empty($entry['image'])) {
                $entry['image'] = $this->prepareImage($entry['image']);
            }
            if ($contact) {
                // Make sure author exists first
                if (empty($entry['author'])) {
                    $entry['author'] = [
                        '@type' => 'Person'
                    ];
                }

                if (empty($entry['author']['name']) && !empty($contact->name)) {
                    $entry['author']['name'] = $contact->name;
                }

                if (empty($entry['author']['email']) && !empty($contact->email_to)) {
                    $entry['author']['email'] = $contact->email_to;
                }

                if (empty($entry['author']['url']) && !empty($contact->webpage)) {
                    $entry['author']['url'] = $contact->webpage;
                }

                if (empty($entry['author']['address']) && !empty($contact->address)) {
                    $entry['author']['address'] = $contact->address;
                }
            }
        }

        $schema->set('@graph', $graph);
    }
}
