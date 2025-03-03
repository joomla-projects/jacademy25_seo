<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') || die;

use Joomla\Application\ApplicationInterface;
use Joomla\Application\SessionAwareWebApplicationInterface;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Webauthn\Authentication;
use Joomla\Plugin\System\Webauthn\CredentialRepository;
use Joomla\Plugin\System\Webauthn\Extension\Webauthn;
use Joomla\Plugin\System\Webauthn\MetadataRepository;
use Joomla\Registry\Registry;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\PublicKeyCredentialSourceRepository;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $construct = function(?Webauthn $plugin = null) use ($container) {
                    $eager   = !$plugin;
                    $app     = Factory::getApplication();
                    $session = $container->has('session') ? $container->get('session') : $this->getSession($app);

                    $db                    = $container->get(DatabaseInterface::class);
                    $credentialsRepository = $container->has(PublicKeyCredentialSourceRepository::class)
                        ? $container->get(PublicKeyCredentialSourceRepository::class)
                        : new CredentialRepository($db);

                    $metadataRepository = null;
                    $params             = new Registry($config['params'] ?? '{}');

                    if ($params->get('attestationSupport', 0) == 1)
                    {
                        $metadataRepository = $container->has(MetadataStatementRepository::class)
                            ? $container->get(MetadataStatementRepository::class)
                            : new MetadataRepository();
                    }

                    $authenticationHelper = $container->has(Authentication::class)
                        ? $container->get(Authentication::class)
                        : new Authentication($app, $session, $credentialsRepository, $metadataRepository);

                    $params = [
                        $container->get(DispatcherInterface::class),
                        (array) PluginHelper::getPlugin('system', 'webauthn'),
                        $authenticationHelper,
                    ];

                    if ($eager) {
                        $plugin = new Webauthn(...$params);
                    } else {
                        $plugin->__construct(...$params);
                    }

                    $plugin->setApplication($app);

                    return $eager ? $plugin : null;
                };

                if (PHP_VERSION_ID >= 80400) {
                    $reflector = new ReflectionClass(Webauthn::class);
                    $plugin    = $reflector->newLazyGhost($construct);
                } else {
                    $plugin = $construct();
                }

                return $plugin;
            }
        );
    }

    /**
     * Get the current application session object
     *
     * @param   ApplicationInterface  $app  The application we are running in
     *
     * @return \Joomla\Session\SessionInterface|null
     *
     * @since  4.2.0
     */
    private function getSession(ApplicationInterface $app)
    {
        return $app instanceof SessionAwareWebApplicationInterface ? $app->getSession() : null;
    }
};
