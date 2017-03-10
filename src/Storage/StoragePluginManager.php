<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
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
