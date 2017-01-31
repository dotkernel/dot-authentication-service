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

use Dot\Authentication\Adapter\Db\CallbackCheckAdapter;
use Interop\Container\ContainerInterface;

/**
 * Class CallbackCheckAdapterFactory
 * @package Dot\Authentication\Factory
 */
class CallbackCheckAdapterFactory extends AbstractAdapterFactory
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return CallbackCheckAdapter
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = [])
    {
        parent::__invoke($container, $requestedName, $options);

        if (isset($options['adapter'])
            && is_string($options['adapter'])
            && $container->has($options['adapter'])
        ) {
            $options['adapter'] = $container->get($options['adapter']);
        }

        if (isset($options['callback_check']) && is_string($options['callback_check'])) {
            if ($container->has($options['callback_check'])) {
                $options['callback_check'] = $container->get($options['callback_check']);
            } elseif (class_exists($options['callback_check'])) {
                $class = $options['callback_check'];
                $options['callback_check'] = new $class();
            }
        }

        return new $requestedName($options);
    }
}
