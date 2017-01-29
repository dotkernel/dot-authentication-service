<?php
/**
 * @copyright: DotKernel
 * @library: dot-authentication-service
 * @author: n3vra
 * Date: 1/28/2017
 * Time: 6:44 PM
 */

declare(strict_types=1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Identity\IdentityInterface;
use Dot\Authentication\Options\AuthenticationOptions;
use Interop\Container\ContainerInterface;
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
     * @param $requestedName
     * @param array $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = [])
    {
        $hydratorManager = $this->getHydratorPluginManager($container);
        if (isset($options['identity_prototype']) && is_string($options['identity_prototype'])) {
            $options['identity_prototype'] = $this->getIdentityPrototype($container, $options['identity_prototype']);
        }

        if (isset($options['identity_hydrator'])
            && is_string($options['identity_hydrator'])
            && $hydratorManager->has($options['identity_hydrator'])) {
            $options['identity_hydrator'] = $hydratorManager->get($options['identity_hydrator']);
        }

        $options['authentication_options'] = $container->get(AuthenticationOptions::class);
    }

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @return IdentityInterface
     */
    public function getIdentityPrototype(ContainerInterface $container, string $name) : IdentityInterface
    {
        $prototype = $name;
        if ($container->has($prototype)) {
            $prototype = $container->get($prototype);
        }

        if (is_string($prototype) && class_exists($prototype)) {
            $prototype = new $prototype();
        }

        if (! $prototype instanceof IdentityInterface) {
            throw new RuntimeException('Identity prototype must be an instance of ' . IdentityInterface::class);
        }

        return $prototype;
    }

    /**
     * @param ContainerInterface $container
     * @return HydratorPluginManager
     */
    public function getHydratorPluginManager(ContainerInterface $container) : HydratorPluginManager
    {
        if ($this->hydratorPluginManager) {
            return $this->hydratorPluginManager;
        }

        if ($container->has('HydratorManager')) {
            $this->hydratorPluginManager = $container->get('HydratorManager');
        } else {
            $this->hydratorPluginManager = new HydratorPluginManager($container, []);
        }

        return $this->hydratorPluginManager;
    }
}
