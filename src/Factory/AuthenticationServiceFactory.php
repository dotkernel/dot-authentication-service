<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Factory;

use Dot\Authentication\Adapter\AdapterPluginManager;
use Dot\Authentication\AuthenticationService;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Options\AuthenticationOptions;
use Dot\Authentication\Storage\StoragePluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class AuthenticationServiceFactory
 * @package Dot\Authentication\Factory
 */
class AuthenticationServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container)
    {
        $moduleOptions = $container->get(AuthenticationOptions::class);

        $adapterPluginManager = $container->get(AdapterPluginManager::class);
        $storagePluginManager = $container->get(StoragePluginManager::class);

        $adapterConfig = $moduleOptions->getAdapter();
        $storageConfig = $moduleOptions->getStorage();

        if (empty($adapterConfig)) {
            throw new RuntimeException('No authentication adapter is set');
        }

        if (empty($storageConfig)) {
            throw new RuntimeException('No authentication storage adapter is set');
        }

        $adapter = $adapterPluginManager->get(key($adapterConfig), current($adapterConfig));
        $storage = $storagePluginManager->get(key($storageConfig), current($storageConfig));

        return new AuthenticationService($adapter, $storage);
    }
}
