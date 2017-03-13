<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Identity\IdentityInterface;
use Dot\Authentication\Options\AuthenticationOptions;
use Psr\Container\ContainerInterface;
use Zend\Hydrator\HydratorPluginManager;

/**
 * Class AbstractAdapterFactory
 * @package Dot\Authentication\Factory
 */
abstract class AbstractAdapterFactory
{
    /** @var  HydratorPluginManager */
    protected $hydratorPluginManager;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null)
    {
        $options = $options ?? [];
        $hydratorManager = $this->getHydratorPluginManager($container);
        if (isset($options['identity_prototype']) && is_string($options['identity_prototype'])) {
            $options['identity_prototype'] = $this->getIdentityPrototype($container, $options['identity_prototype']);
        }

        if (isset($options['identity_hydrator'])
            && is_string($options['identity_hydrator'])
            && $hydratorManager->has($options['identity_hydrator'])
        ) {
            $options['identity_hydrator'] = $hydratorManager->get($options['identity_hydrator']);
        }

        $options['authentication_options'] = $container->get(AuthenticationOptions::class);

        return new $requestedName($options);
    }

    /**
     * @param ContainerInterface $container
     * @return HydratorPluginManager
     */
    public function getHydratorPluginManager(ContainerInterface $container): HydratorPluginManager
    {
        if (!$this->hydratorPluginManager) {
            if ($container->has('HydratorManager')) {
                $this->hydratorPluginManager = $container->get('HydratorManager');
            } else {
                $this->hydratorPluginManager = new HydratorPluginManager($container, []);
            }
        }

        return $this->hydratorPluginManager;
    }

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @return IdentityInterface
     */
    public function getIdentityPrototype(ContainerInterface $container, string $name): IdentityInterface
    {
        $prototype = $name;
        if ($container->has($prototype)) {
            $prototype = $container->get($prototype);
        }

        if (is_string($prototype) && class_exists($prototype)) {
            $prototype = new $prototype();
        }

        if (!$prototype instanceof IdentityInterface) {
            throw new RuntimeException('Identity prototype must be an instance of ' . IdentityInterface::class);
        }

        return $prototype;
    }
}
