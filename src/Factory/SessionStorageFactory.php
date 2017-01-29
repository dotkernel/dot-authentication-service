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

use Dot\Authentication\Storage\SessionStorage;
use Interop\Container\ContainerInterface;
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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = [])
    {
        if (isset($options['session_manager']) && is_string($options['session_manager'])) {
            if ($container->has($options['session_manager'])) {
                $options['session_manager'] = $container->get($options['session_manager']);
            } elseif (class_exists($options['session_manager'])) {
                $class = $options['session_manager'];
                $options['session_manager'] = new $class();
            }
        }

        //lets try to get the default session manager from the container, if it is available
        if (! $options['session_manager'] instanceof ManagerInterface) {
            if ($container->has(ManagerInterface::class)) {
                $options['session_manager'] = $container->get(ManagerInterface::class);
            }
        }

        return new $requestedName($options);
    }
}
