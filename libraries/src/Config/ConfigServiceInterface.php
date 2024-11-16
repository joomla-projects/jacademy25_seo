<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Config;

use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Allows manipulating the config form for this component.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ConfigServiceInterface
{
    /**
     * Prepares the config form
     *
     * @param   Form          $form  The form to change
     * @param   array|object  $data  The form data
     *
     * @return   void
     */
    public function prepareForm(Form $form, $data) : void;
}
