<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Opengraph
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Opengraph\Extension;

use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * OpenGraph Metadata plugin.
 *
 * @since  __DEPLOY_VERSION__
 */

final class PlgSystemOpengraph extends CMSPlugin implements SubscriberInterface
{


    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead' => 'onBeforeCompileHead',
        ];
    }

}
