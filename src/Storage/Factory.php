<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Storage;

use Dot\Authentication\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

/**
 * Class StorageFactory
 * @package Dot\Authentication\Factory
 */
class Factory
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

    public function create(array $options): StorageInterface
    {
        $type = $options['type'] ?? null;
        if (!$type) {
            throw new RuntimeException('Storage adapter type is not specified in the config');
        }

        if (!$this->getStoragePluginManager()->has($type)) {
            throw new RuntimeException(sprintf('Storage adapter type %s is not found in the plugin manager', $type));
        }

        $options = $options['options'] ?? null;
        return $this->getStoragePluginManager()->get($type, $options);
    }

    /**
     * @return StoragePluginManager
     */
    public function getStoragePluginManager(): StoragePluginManager
    {
        if (!$this->storagePluginManager) {
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
