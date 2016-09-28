<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Factory;

use Dot\Authentication\Options\AuthenticationOptions;
use Interop\Container\ContainerInterface;

/**
 * Class AuthenticationOptionsFactory
 * @package Dot\Authentication\Factory
 */
class AuthenticationOptionsFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthenticationOptions
     */
    public function __invoke(ContainerInterface $container)
    {
        return new AuthenticationOptions($container->get('config')['dot_authentication']);
    }
}