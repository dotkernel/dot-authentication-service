<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Factory;

use Dot\Authentication\Adapter\HttpAdapter;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Http\ResolverPluginManager;
use Dot\Authentication\Options\AuthenticationOptions;
use Dot\Helpers\DependencyHelperTrait;
use Interop\Container\ContainerInterface;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;

/**
 * Class HttpAdapterFactory
 * @package Dot\Authentication\Factory
 */
class HttpAdapterFactory
{
    use DependencyHelperTrait;

    /**
     * @param ContainerInterface $container
     * @param $resolvedName
     * @param array $options
     * @return HttpAdapter
     */
    public function __invoke(ContainerInterface $container, $resolvedName, array $options = [])
    {
        $this->container = $container;

        /** @var AuthenticationOptions $moduleOptions */
        $moduleOptions = $container->get(AuthenticationOptions::class);

        //get identity and its hydrator objects, as set in config
        $identity = $this->getDependencyObject($container, $moduleOptions->getIdentityClass());
        if (!is_object($identity)) {
            throw new RuntimeException('No valid identity prototype specified');
        }
        $hydrator = $this->getDependencyObject($container, $moduleOptions->getIdentityHydratorClass());
        if (!$hydrator instanceof HydratorInterface) {
            $hydrator = new ClassMethods(false);
        }

        $resolverPluginManager = $container->get(ResolverPluginManager::class);

        $resolverConfig = isset($options['resolvers']) ? $options['resolvers'] : [];

        $basicResolver = null;
        $digestResolver = null;
        if (isset($resolverConfig['basic']) && is_array($resolverConfig['basic'])) {
            $basicResolver = $resolverPluginManager->get(
                $resolverConfig['basic']['name'],
                $resolverConfig['basic']['options']
            );
        }

        if (isset($resolverConfig['digest']) && is_array($resolverConfig['digest'])) {
            $digestResolver = $resolverPluginManager->get(
                $resolverConfig['digest']['name'],
                $resolverConfig['digest']['options']
            );
        }

        if (!$basicResolver && !$digestResolver) {
            throw new RuntimeException("At least one http resolver must be set in order to use the adapter");
        }

        $httpAdapter = new HttpAdapter($moduleOptions, $options, $basicResolver, $digestResolver);
        $httpAdapter->setIdentityPrototype($identity);
        $httpAdapter->setIdentityHydrator($hydrator);

        return $httpAdapter;
    }
}
