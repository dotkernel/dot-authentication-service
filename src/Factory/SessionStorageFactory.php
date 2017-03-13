<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Storage\SessionStorage;
use Psr\Container\ContainerInterface;
use Zend\Session\ManagerInterface;

/**
 * Class SessionStorageFactory
 * @package Dot\Authentication\Factory
 */
class SessionStorageFactory
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return SessionStorage
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null)
    {
        $options = $options ?? [];
        if (isset($options['session_manager']) && is_string($options['session_manager'])) {
            if ($container->has($options['session_manager'])) {
                $options['session_manager'] = $container->get($options['session_manager']);
            } elseif (class_exists($options['session_manager'])) {
                $class = $options['session_manager'];
                $options['session_manager'] = new $class();
            }
        } elseif ($container->has(ManagerInterface::class)) {
            $options['session_manager'] = $container->get(ManagerInterface::class);
        }

        return new $requestedName($options);
    }
}
