<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication;

use Dot\Authentication\Exception\InvalidArgumentException;
use Dot\Authentication\Exception\RuntimeException;
use Interop\Container\ContainerInterface;
use Zend\Hydrator\HydratorInterface;

/**
 * Class IdentityFactoryProviderTrait
 * @package Dot\Authentication
 */
trait IdentityFactoryProviderTrait
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param $identityPrototypeName
     * @return mixed|null
     * @throws \Exception
     */
    public function getIdentityPrototype($identityPrototypeName)
    {
        $identityPrototype = null;
        if (!$identityPrototypeName) {
            throw new InvalidArgumentException("Identity prototype is required and cannot be empty");
        }
        //check if is a service name
        $identityPrototype = $this->container->has($identityPrototypeName)
            ? $this->container->get($identityPrototypeName)
            : $identityPrototypeName;

        if (is_string($identityPrototype)) {
            if (!class_exists($identityPrototype)) {
                throw new RuntimeException("Identity prototype is not a valid class name");
            }

            $identityPrototype = new $identityPrototype;
        }

        if (!is_object($identityPrototype)) {
            throw new RuntimeException("Identity prototype is not a valid object");
        }

        return $identityPrototype;
    }

    /**
     * @param $identityHydratorName
     * @return mixed|null
     * @throws \Exception
     */
    public function getIdentityHydrator($identityHydratorName)
    {
        $identityHydrator = null;
        if (!$identityHydratorName) {
            throw new InvalidArgumentException("Identity hydrator is required and cannot be empty");
        }
        //check if is a service name
        $identityHydrator = $this->container->has($identityHydratorName)
            ? $this->container->get($identityHydratorName)
            : $identityHydratorName;

        if (is_string($identityHydrator)) {
            if (!class_exists($identityHydrator)) {
                throw new RuntimeException("Identity hydrator is not a valid class name");
            }

            $identityHydrator = new $identityHydrator;
        }

        if (!$identityHydrator instanceof HydratorInterface) {
            throw new RuntimeException("Identity hydrator must implement " . HydratorInterface::class);
        }

        return $identityHydrator;
    }
}