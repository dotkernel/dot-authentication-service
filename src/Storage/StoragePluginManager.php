<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

declare(strict_types = 1);

namespace Dot\Authentication\Storage;

use Dot\Authentication\Factory\SessionStorageFactory;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Class StoragePluginManager
 * @package Dot\Authentication\Storage
 */
class StoragePluginManager extends AbstractPluginManager
{
    protected $instanceOf = StorageInterface::class;

    protected $factories = [
        SessionStorage::class => SessionStorageFactory::class,
        NonPersistentStorage::class => InvokableFactory::class,
    ];

    protected $aliases = [
        'session' => SessionStorage::class,
        'Session' => SessionStorage::class,
        'sessionstorage' => SessionStorage::class,
        'sessionStorage' => SessionStorage::class,
        'SessionStorage' => SessionStorage::class,

        'nonpersistent' => NonPersistentStorage::class,
        'nonPersistent' => NonPersistentStorage::class,
        'NonPersistent' => NonPersistentStorage::class,
        'nonpersistentstorage' => NonPersistentStorage::class,
        'nonPersistentStorage' => NonPersistentStorage::class,
        'NonPersistentStorage' => NonPersistentStorage::class,
    ];
}
