<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Config\ConfigServiceInterface;
use Joomla\CMS\Config\ConfigServiceTrait;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\User\UserHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_config
 *
 * @since  __DEPLOY_VERSION__
 */
class JoomlaupdateComponent extends MVCComponent implements ConfigServiceInterface
{
    use ConfigServiceTrait;
}
