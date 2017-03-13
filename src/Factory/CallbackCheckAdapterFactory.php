<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Adapter\Db\CallbackCheckAdapter;
use Psr\Container\ContainerInterface;

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
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null)
    {
        $options = $options ?? [];
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

        return parent::__invoke($container, $requestedName, $options);
    }
}
