<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

declare(strict_types=1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Adapter\AdapterPluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class AdapterPluginManagerFactory
 * @package Dot\Authentication\Factory
 */
class AdapterPluginManagerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AdapterPluginManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['dot_authentication']['adapter_manager'];
        return new AdapterPluginManager($container, $config);
    }
}
