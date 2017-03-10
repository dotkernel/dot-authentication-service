<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication;

use Dot\Authentication\Adapter\AdapterPluginManager;
use Dot\Authentication\Factory\AdapterPluginManagerFactory;
use Dot\Authentication\Factory\AuthenticationOptionsFactory;
use Dot\Authentication\Factory\AuthenticationServiceFactory;
use Dot\Authentication\Factory\ResolverPluginManagerFactory;
use Dot\Authentication\Factory\StoragePluginManagerFactory;
use Dot\Authentication\Http\ResolverPluginManager;
use Dot\Authentication\Options\AuthenticationOptions;
use Dot\Authentication\Storage\StoragePluginManager;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),

            'dot_authentication' => [

                'adapter' => [],

                'storage' => [],

                'adapter_manager' => [],

                'storage_manager' => [],

                'resolver_manager' => [],
            ]
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                AuthenticationService::class => AuthenticationServiceFactory::class,
                AdapterPluginManager::class => AdapterPluginManagerFactory::class,
                ResolverPluginManager::class => ResolverPluginManagerFactory::class,
                StoragePluginManager::class => StoragePluginManagerFactory::class,
                AuthenticationOptions::class => AuthenticationOptionsFactory::class,
            ],
            'aliases' => [
                AuthenticationInterface::class => AuthenticationService::class,
            ]
        ];
    }
}
