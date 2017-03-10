<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

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
     * @param $requestedName
     * @return AuthenticationOptions
     */
    public function __invoke(ContainerInterface $container, string $requestedName)
    {
        return new $requestedName($container->get('config')['dot_authentication']);
    }
}
