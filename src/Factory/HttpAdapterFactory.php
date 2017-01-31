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

use Dot\Authentication\Adapter\HttpAdapter;
use Dot\Authentication\Http\ResolverPluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class HttpAdapterFactory
 * @package Dot\Authentication\Factory
 */
class HttpAdapterFactory extends AbstractAdapterFactory
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return HttpAdapter
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = [])
    {
        parent::__invoke($container, $requestedName, $options);

        /** @var ResolverPluginManager $resolverPluginManager */
        $resolverPluginManager = $container->get(ResolverPluginManager::class);

        if (isset($options['basic_resolver']) && is_array($options['basic_resolver'])
            && isset($options['basic_resolver']['name'])
            && $resolverPluginManager->has($options['basic_resolver']['name'])
        ) {
            $options['basic_resolver'] = $resolverPluginManager->get(
                $options['basic_resolver']['name'],
                isset($options['basic_resolver']['options']) ? $options['basic_resolver']['options'] : []
            );
        }

        if (isset($options['digest_resolver']) && is_array($options['digest_resolver'])
            && isset($options['digest_resolver']['name'])
            && $resolverPluginManager->has($options['digest_resolver']['name'])
        ) {
            $options['digest_resolver'] = $resolverPluginManager->get(
                $options['digest_resolver']['name'],
                isset($options['digest_resolver']['options']) ? $options['digest_resolver']['options'] : []
            );
        }

        return new $requestedName($options);
    }
}
