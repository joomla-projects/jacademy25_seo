<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Uri\UriHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  1.7.0
 */
class UrlRule extends FormRule
{
    /**
     * Method to test an external or internal url for all valid parts.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   1.7.0
     * @link    https://www.w3.org/Addressing/URL/url-spec.txt
     * @see     \Joomla\String\StringHelper
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        // If the field is empty and not required, the field is valid.
        $required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

        if (!$required && empty($value)) {
            return true;
        }

        // Check the value for XSS payloads
        if ((string) $element['disableXssCheck'] !== 'true' && InputFilter::checkAttribute(['href', $value])) {
            $element->addAttribute('message', Text::sprintf('JLIB_FORM_VALIDATE_FIELD_URL_INJECTION_DETECTED', $element['name']));
            return false;
        }

        $urlParts = UriHelper::parse_url($value);

        // See https://www.w3.org/Addressing/URL/url-spec.txt
        // Use the full list or optionally specify a list of permitted schemes.
        if ($element['schemes'] == '') {
            $scheme = ['http', 'https', 'ftp', 'ftps', 'gopher', 'mailto', 'news', 'prospero', 'telnet', 'rlogin', 'sftp', 'tn3270', 'wais',
                'mid', 'cid', 'nntp', 'tel', 'urn', 'ldap', 'file', 'fax', 'modem', 'git', ];
        } else {
            $scheme = explode(',', $element['schemes']);
        }

        /*
         * Note that parse_url() does not always parse accurately without a scheme,
         * but at least the path should be set always. Note also that parse_url()
         * returns False for seriously malformed URLs instead of an associative array.
         * @link https://www.php.net/manual/en/function.parse-url.php
         */
        if ($urlParts === false || !\array_key_exists('scheme', $urlParts)) {
            /*
             * The function parse_url() returned false (seriously malformed URL) or no scheme
             * was found and the relative option is not set: in both cases the field is not valid.
             */
            if ($urlParts === false || !$element['relative']) {
                $element->addAttribute('message', Text::sprintf('JLIB_FORM_VALIDATE_FIELD_URL_SCHEMA_MISSING', $value, implode(', ', $scheme)));

                return false;
            }

            // The best we can do for the rest is make sure that the path exists and is valid UTF-8.
            if (!\array_key_exists('path', $urlParts) || !StringHelper::valid((string) $urlParts['path'])) {
                return false;
            }

            // The internal URL seems to be good.
            return true;
        }

        // Scheme found, check all parts found.
        $urlScheme = (string) $urlParts['scheme'];
        $urlScheme = strtolower($urlScheme);

        if (!\in_array($urlScheme, $scheme)) {
            return false;
        }

        // For some schemes there must be two slashes.
        $scheme = ['http', 'https', 'ftp', 'ftps', 'gopher', 'wais', 'prospero', 'sftp', 'telnet', 'git'];

        if (\in_array($urlScheme, $scheme) && substr($value, \strlen($urlScheme), 3) !== '://') {
            return false;
        }

        // The best we can do for the rest is make sure that the strings are valid UTF-8
        // and the port is an integer.
        if (\array_key_exists('host', $urlParts) && !StringHelper::valid((string) $urlParts['host'])) {
            return false;
        }

        if (\array_key_exists('port', $urlParts) && 0 === (int) $urlParts['port']) {
            return false;
        }

        if (\array_key_exists('path', $urlParts) && !StringHelper::valid((string) $urlParts['path'])) {
            return false;
        }

        return true;
    }
}
