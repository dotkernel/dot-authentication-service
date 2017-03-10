<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Http;

use Dot\Authentication\Factory\FileResolverFactory;
use Zend\Authentication\Adapter\Http\FileResolver;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class ResolverPluginManager
 * @package Dot\Authentication\Http
 */
class ResolverPluginManager extends AbstractPluginManager
{
    protected $instanceOf = ResolverInterface::class;

    protected $factories = [
        FileResolver::class => FileResolverFactory::class,
    ];

    protected $aliases = [
        'file' => FileResolver::class,
        'File' => FileResolver::class,
        'fileresolver' => FileResolver::class,
        'fileResolver' => FileResolver::class,
        'FileResolver' => FileResolver::class,
    ];
}
