<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

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
}
