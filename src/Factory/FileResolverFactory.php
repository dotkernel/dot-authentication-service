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

use Dot\Authentication\Exception\RuntimeException;
use Interop\Container\ContainerInterface;
use Zend\Authentication\Adapter\Http\FileResolver;

/**
 * Class FileResolverFactory
 * @package Dot\Authentication\Factory
 */
class FileResolverFactory
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return FileResolver
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = [])
    {
        $path = $options['path'] ?? '';

        if (empty($path)) {
            throw new RuntimeException("FileResolver requires a `path` parameter to be set in config");
        }

        return new $requestedName($path);
    }
}
