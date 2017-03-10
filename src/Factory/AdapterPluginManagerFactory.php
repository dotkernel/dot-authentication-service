<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

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
