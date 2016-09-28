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
use Dot\Authentication\IdentityFactoryProviderTrait;
use Dot\Authentication\Options\AuthenticationOptions;
use Interop\Container\ContainerInterface;

/**
 * Class HttpAdapterFactory
 * @package Dot\Authentication\Factory
 */
class HttpAdapterFactory
{
    use IdentityFactoryProviderTrait;

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

        //these are trait's methods, to get objects directly or from container
        $identityPrototype = $this->getIdentityPrototype($moduleOptions->getIdentityClass());
        $identityHydrator = $this->getIdentityHydrator($moduleOptions->getIdentityHydratorClass());

        $resolverPluginManager = $container->get(ResolverPluginManager::class);

        $resolverConfig = isset($options['resolvers']) ? $options['resolvers'] : [];

        $basicResolver = null;
        $digestResolver = null;
        if(isset($resolverConfig['basic']) && is_array($resolverConfig['basic']))
        {
            $basicResolver = $resolverPluginManager->get($resolverConfig['basic']['name'], $resolverConfig['basic']['options']);
        }

        if(isset($resolverConfig['digest']) && is_array($resolverConfig['digest']))
        {
            $digestResolver = $resolverPluginManager->get($resolverConfig['digest']['name'], $resolverConfig['digest']['options']);
        }

        if(!$basicResolver && !$digestResolver) {
            throw new RuntimeException("At least one http resolver must be set in order to use the adapter");
        }

        $httpAdapter = new HttpAdapter($moduleOptions, $options, $basicResolver, $digestResolver);
        $httpAdapter->setIdentityPrototype($identityPrototype);
        $httpAdapter->setIdentityHydrator($identityHydrator);
        
        return $httpAdapter;
    }
}