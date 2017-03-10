<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Storage\StoragePluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class StoragePluginManagerFactory
 * @package Dot\Authentication\Factory
 */
class StoragePluginManagerFactory
{
    /**
     * @param ContainerInterface $container
     * @return StoragePluginManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['dot_authentication']['storage_manager'];
        return new StoragePluginManager($container, $config);
    }
}
