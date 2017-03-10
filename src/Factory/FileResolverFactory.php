<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
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
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null)
    {
        $options = $options ?? [];
        $path = $options['path'] ?? '';

        if (empty($path)) {
            throw new RuntimeException("FileResolver requires a `path` parameter to be set in config");
        }

        return new $requestedName($path);
    }
}
