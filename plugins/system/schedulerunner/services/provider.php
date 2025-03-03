<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.schedulerunner
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\ScheduleRunner\Extension\ScheduleRunner;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $construct = function(?ScheduleRunner $plugin = null) use ($container) {
                    $eager  = !$plugin;
                    $params = [
                        $container->get(DispatcherInterface::class),
                        (array) PluginHelper::getPlugin('system', 'schedulerunner'),
                    ];

                    if ($eager) {
                        $plugin = new ScheduleRunner(...$params);
                    } else {
                        $plugin->__construct(...$params);
                    }

                    $plugin->setApplication(Factory::getApplication());

                    return $eager ? $plugin : null;
                };

                if (PHP_VERSION_ID >= 80400) {
                    $reflector = new ReflectionClass(ScheduleRunner::class);
                    $plugin    = $reflector->newLazyGhost($construct);
                } else {
                    $plugin = $construct();
                }

                return $plugin;
            }
        );
    }
};
