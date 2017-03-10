<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Adapter\AdapterPluginManager;
use Dot\Authentication\Adapter\Factory as AdapterFactory;
use Dot\Authentication\AuthenticationService;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Options\AuthenticationOptions;
use Dot\Authentication\Storage\Factory as StorageFactory;
use Dot\Authentication\Storage\StoragePluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class AuthenticationServiceFactory
 * @package Dot\Authentication\Factory
 */
class AuthenticationServiceFactory
{
    /** @var  AdapterFactory */
    protected $adapterFactory;

    /** @var  StorageFactory */
    protected $storageFactory;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        $authenticationOptions = $container->get(AuthenticationOptions::class);

        if ($container->has(AdapterPluginManager::class)) {
            $this->adapterFactory = new AdapterFactory($container, $container->get(AdapterPluginManager::class));
        }

        if ($container->has(StoragePluginManager::class)) {
            $this->storageFactory = new StorageFactory($container, $container->get(StoragePluginManager::class));
        }

        $adapterConfig = $authenticationOptions->getAdapter();
        $storageConfig = $authenticationOptions->getStorage();

        if (empty($adapterConfig)) {
            throw new RuntimeException('No authentication adapter config is set');
        }

        if (empty($storageConfig)) {
            throw new RuntimeException('No authentication storage adapter config is set');
        }

        $adapter = $this->getAdapterFactory($container)->create($adapterConfig);
        $storage = $this->getStorageFactory($container)->create($storageConfig);

        return new $requestedName($adapter, $storage);
    }

    /**
     * @param ContainerInterface $container
     * @return AdapterFactory
     */
    public function getAdapterFactory(ContainerInterface $container): AdapterFactory
    {
        if (!$this->adapterFactory) {
            $this->adapterFactory = new AdapterFactory($container);
        }

        return $this->adapterFactory;
    }

    /**
     * @param ContainerInterface $container
     * @return StorageFactory
     */
    public function getStorageFactory(ContainerInterface $container): StorageFactory
    {
        if (!$this->storageFactory) {
            $this->storageFactory = new StorageFactory($container);
        }
        return $this->storageFactory;
    }
}
