<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

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