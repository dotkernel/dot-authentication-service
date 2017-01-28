<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

declare(strict_types=1);

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
