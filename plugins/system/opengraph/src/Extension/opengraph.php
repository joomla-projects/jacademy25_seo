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




\defined('_JEXEC') or die;

final class Opengraph extends CMSPlugin implements SubscriberInterface
{
    protected $app;
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead' => 'onBeforeCompileHead',
            'onContentPrepareForm' => 'onContentPrepareForm',
        ];
    }

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

    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $app = $this->getApplication();

        if (!$app->isClient('administrator')) {
            return;
        }

        $form = $event->getForm();
        $name = $form->getName();

        if ($name === 'com_content.article') {
            $xml = __DIR__ . '/../forms/opengraph.xml';
            if (file_exists($xml)) {
                $form->loadFile($xml, false);
            }
        }
    }

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

    private function setMetaData(Document $document, string $name, ?string $value, string $attributeType): void
    {
        if (!empty($value)) {
            $document->setMetaData($name, $value, $attributeType);
        }
    }

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