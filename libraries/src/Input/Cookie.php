<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Input;

use Joomla\CMS\Filter\InputFilter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Input Cookie Class
 *
 * @since       1.7.0
 *
 * @deprecated   4.3 will be removed in 6.0.
 *               Use Joomla\Input\Cookie instead
 */
class Cookie extends Input
{
    /**
     * Constructor.
     *
     * @param   ?array  $source   Ignored.
     * @param   array   $options  Array of configuration parameters (Optional)
     *
     * @since   1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Cookie instead
     */
    public function __construct(?array $source = null, array $options = [])
    {
        $this->filter = $options['filter'] ?? InputFilter::getInstance();

        // Set the data source.
        $this->data = &$_COOKIE;

        // Set the options for the class.
        $this->options = $options;
    }

    /**
     * Sets a value
     *
     * @param   string   $name      Name of the value to set.
     * @param   mixed    $value     Value to assign to the input.
     * @param   array    $options   An associative array which may have any of the keys expires, path, domain,
     *                              secure, httponly and samesite. The values have the same meaning as described
     *                              for the parameters with the same name. The value of the samesite element
     *                              should be either Lax or Strict. If any of the allowed options are not given,
     *                              their default values are the same as the default values of the explicit
     *                              parameters. If the samesite element is omitted, no SameSite cookie attribute
     *                              is set.
     *
     * @return  void
     *
     * @link    http://www.ietf.org/rfc/rfc2109.txt
     * @see     setcookie()
     * @since   1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Cookie instead
     */
    public function set($name, $value, $options = [])
    {
        // BC layer to convert old method parameters.
        if (\is_array($options) === false) {
            trigger_deprecation(
                'joomla/input',
                '1.4.0',
                'The %s($name, $value, $expire, $path, $domain, $secure, $httpOnly) signature is deprecated and'
                . ' will not be supported once support'
                . ' for PHP 7.2 and earlier is dropped, use the %s($name, $value, $options) signature instead',
                __METHOD__,
                __METHOD__
            );

            $argList = \func_get_args();

            $options = [
                'expires'  => $argList[2] ?? 0,
                'path'     => $argList[3] ?? '',
                'domain'   => $argList[4] ?? '',
                'secure'   => $argList[5] ?? false,
                'httponly' => $argList[6] ?? false,
            ];
        }

        // Set the cookie
        if (\is_array($value)) {
            foreach ($value as $key => $val) {
                setcookie($name . sprintf('[%s]', $key), (string) $val, $options);
            }
        } else {
            setcookie($name, (string) $value, $options);
        }

        $this->data[$name] = $value;
    }
}
