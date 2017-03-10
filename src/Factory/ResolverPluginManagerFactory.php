<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
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
