<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Adapter;

use Dot\Authentication\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

/**
 * Class AdapterFactory
 * @package Dot\Authentication\Factory
 */
class Factory
{
    /** @var  ContainerInterface */
    protected $container;

    /** @var  AdapterPluginManager */
    protected $adapterPluginManager;

    /**
     * AdapterFactory constructor.
     * @param ContainerInterface $container
     * @param AdapterPluginManager|null $adapterPluginManager
     */
    public function __construct(ContainerInterface $container, AdapterPluginManager $adapterPluginManager = null)
    {
        $this->container = $container;
        if ($adapterPluginManager) {
            $this->setAdapterPluginManager($adapterPluginManager);
        }
    }

    /**
     * @param array $options
     * @return AdapterInterface
     */
    public function create(array $options): AdapterInterface
    {
        $type = $options['type'] ?? null;
        if (!$type) {
            throw new RuntimeException('Adapter type is not specified in the config');
        }

        if (!$this->getAdapterPluginManager()->has($type)) {
            throw new RuntimeException(sprintf('Adapter type %s is not found in the plugin manager', $type));
        }

        $options = $options['options'] ?? null;
        return $this->getAdapterPluginManager()->get($type, $options);
    }

    /**
     * @return AdapterPluginManager
     */
    public function getAdapterPluginManager(): AdapterPluginManager
    {
        if (!$this->adapterPluginManager) {
            $this->adapterPluginManager = new AdapterPluginManager($this->container, []);
        }
        return $this->adapterPluginManager;
    }

    /**
     * @param AdapterPluginManager $adapterPluginManager
     */
    public function setAdapterPluginManager(AdapterPluginManager $adapterPluginManager)
    {
        $this->adapterPluginManager = $adapterPluginManager;
    }
}
