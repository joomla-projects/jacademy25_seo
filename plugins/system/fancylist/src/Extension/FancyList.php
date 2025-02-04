<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.fancylist
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\FancyList\Extension;

use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Allows to set default values for 'data-max-results' and 'data-max-render' attributes
 * in the fancylist form fields.
 *
 * @since  __DEPLOY_VERSION__
 */
final class FancyList extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns the list of event subscribers.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepareForm' => 'onContentPrepareForm',
        ];
    }

    /**
     * Handles the onContentPrepareForm event to dynamically set default values for
     * 'data-max-results' and 'data-max-render' attributes in the form fields.
     *
     * Modifies forms with the names 'com_content.article' and 'com_content.articles.filter'.
     * For the 'com_content.articles.filter' form, it adds attributes to the 'category_id' field.
     * For 'com_content.article', it checks for the 'catid' field and sets the attributes if present.
     *
     * @param   PrepareFormEvent  $event  The event containing the form to be modified.
     *
     * @return  bool  True if the form processing should continue, false otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */

    public function onContentPrepareForm(PrepareFormEvent $event): bool
    {
        $form = $event->getForm();
        if (!\in_array($form->getName(), ['com_content.article', 'com_content.articles.filter'])) {
            return true;
        }

        // Fetch the configuration setting
        $maxResults = (string) $this->params->get('max_results', '10');
        $maxRender  = (string) $this->params->get('max_render', '-1');

        if ($form->getName() === 'com_content.articles.filter') {

            // Get the field from the SimpleXMLElement structure
            $xml = $form->getXml();

            if (!$xml instanceof \SimpleXMLElement) {
                return true;
            }

            // Get the category_id field
            $field = $xml->xpath("//field[@name='category_id']")[0] ?? null;

            if ($field) {
                $field->addAttribute('data-max-results', $maxResults);
                $field->addAttribute('data-max-render', $maxRender);
            }

            return true;
        }

        // Check if the field exists in the form
        if ($form->getField('catid')) {
            // Inject the value into the form field's attributes
            $form->setFieldAttribute('catid', 'data-max-results', $maxResults);
            $form->setFieldAttribute('catid', 'data-max-render', $maxRender);
        }

        return true;
    }
}
