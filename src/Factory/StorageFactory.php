<?php
/**
 * @copyright: DotKernel
 * @library: dot-authentication-service
 * @author: n3vra
 * Date: 1/29/2017
 * Time: 2:30 PM
 */

declare(strict_types=1);

namespace Dot\Authentication\Factory;

use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Storage\StorageInterface;
use Dot\Authentication\Storage\StoragePluginManager;
use Interop\Container\ContainerInterface;

/**
 * Class StorageFactory
 * @package Dot\Authentication\Factory
 */
class StorageFactory
{
    /** @var  ContainerInterface */
    protected $container;

    /** @var  StoragePluginManager */
    protected $storagePluginManager;

    public function __construct(ContainerInterface $container, StoragePluginManager $storagePluginManager = null)
    {
        $this->container = $container;
        if ($this->storagePluginManager) {
            $this->setStoragePluginManager($storagePluginManager);
        }
    }

    public function create(array $options) : StorageInterface
    {
        $type = $options['type'] ?? null;
        if (! $type) {
            throw new RuntimeException('Storage adapter type is not specified in the config');
        }

        if (! $this->getStoragePluginManager()->has($type)) {
            throw new RuntimeException(sprintf('Storage adapter type %s is not found in the plugin manager', $type));
        }

        $options = $options['options'] ?? [];
        return $this->getStoragePluginManager()->get($type, $options);
    }

    /**
     * @return StoragePluginManager
     */
    public function getStoragePluginManager(): StoragePluginManager
    {
        if (! $this->storagePluginManager) {
            $this->storagePluginManager = new StoragePluginManager($this->container, []);
        }
        return $this->storagePluginManager;
    }

    /**
     * @param StoragePluginManager $storagePluginManager
     */
    public function setStoragePluginManager(StoragePluginManager $storagePluginManager)
    {
        $this->storagePluginManager = $storagePluginManager;
    }
}
