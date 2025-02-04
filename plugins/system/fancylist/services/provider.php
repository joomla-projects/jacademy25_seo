<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.fancylist
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\FancyList\Extension\FancyList;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $fancyListPlugin = new FancyList(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'fancylist')
                );
                $fancyListPlugin->setApplication(Factory::getApplication());

                return $fancyListPlugin;
            }
        );
    }
};
