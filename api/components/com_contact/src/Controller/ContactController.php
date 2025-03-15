<?php

/**
 * @package     Joomla.API
 * @subpackage  com_contact
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Api\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception\SendEmail;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;
use Joomla\String\Inflector;
use Tobscure\JsonApi\Exception\InvalidParameterException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The contact controller
 *
 * @since  4.0.0
 */
class ContactController extends ApiController implements UserFactoryAwareInterface
{
    use UserFactoryAwareTrait;

    /**
     * The content type of the item.
     *
     * @var string
     * @since 4.0.0
     */
    protected $contentType = 'contacts';

    /**
     * The default view for the display method.
     *
     * @var string
     * @since 3.0
     */
    protected $default_view = 'contacts';

    /**
     * Submit contact form
     *
     * @param integer $id Leave empty if you want to retrieve data from the request.
     * @return static A \JControllerLegacy object to support chaining.
     *
     * @since 4.0.0
     */
    public function submitForm($id = null)
    {
        if ($id === null) {
            $id = $this->input->post->get('id', 0, 'int');
        }

        $modelName = Inflector::singularize($this->contentType);

        /** @var \Joomla\Component\Contact\Site\Model\ContactModel $model */
        $model = $this->getModel($modelName, 'Site');

        if (!$model) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        $model->setState('filter.published', 1);

        $data    = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');
        $contact = $model->getItem($id);

        if ($contact->id === null) {
            throw new RouteNotFoundException('Item does not exist');
        }

        $contactParams = new Registry($contact->params);

        if (!$contactParams->get('show_email_form')) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_DISPLAY_EMAIL_FORM'));
        }

        // Contact plugins
        PluginHelper::importPlugin('contact');

        Form::addFormPath(JPATH_COMPONENT_SITE . '/forms');

        // Validate the posted data.
        $form = $model->getForm();

        if (!$form) {
            throw new \RuntimeException($model->getError(), 500);
        }

        if (!$model->validate($form, $data)) {
            $errors   = $model->getErrors();
            $messages = [];

            for ($i = 0, $n = \count($errors); $i < $n && $i < 3; $i++) {
                $messages[] = $errors[$i] instanceof \Exception ? $errors[$i]->getMessage() : $errors[$i];
            }

            throw new InvalidParameterException(implode("\n", $messages));
        }

        // Passed validation, process plugins
        $event = $this->getDispatcher()->dispatch('onSubmitContact', new SubmitContactEvent('onSubmitContact', [
            'subject' => $contact,
            'data'    => &$data
        ]));

        // Get the final data
        $data   = $event->getArgument('data', $data);
        $params = ComponentHelper::getParams('com_contact');

        if (!$params->get('custom_reply')) {
            $sent = $this->_sendEmail($data, $contact, $params->get('show_email_copy', 0));
        }

        if (!$sent) {
            throw new SendEmail('Error sending message');
        }

        return $this;
    }
}