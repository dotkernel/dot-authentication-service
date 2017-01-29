<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

declare(strict_types = 1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Http\ResolverPluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class ResolverPluginManagerFactory
 * @package Dot\Authentication\Factory
 */
class ResolverPluginManagerFactory
{
    /**
     * @param ContainerInterface $container
     * @return ResolverPluginManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['dot_authentication']['resolver_manager'];
        return new ResolverPluginManager($container, $config);
    }
}
