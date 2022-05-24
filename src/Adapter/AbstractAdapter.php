<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Adapter;

use Dot\Authentication\Exception\InvalidArgumentException;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Identity\IdentityInterface;
use Dot\Authentication\Options\AuthenticationOptions;
use Laminas\Hydrator\ClassMethodsHydrator;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Hydrator\HydratorInterface;

/**
 * Class AbstractAdapter
 * @package Dot\Authentication\Adapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /** @var  ServerRequestInterface */
    protected $request;

    /** @var  IdentityInterface */
    protected $identityPrototype;

    /** @var  HydratorInterface */
    protected $identityHydrator;

    /** @var  AuthenticationOptions */
    protected $authenticationOptions;

    /**
     * AbstractAdapter constructor.
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $options = $options ?? [];
        if (isset($options['identity_prototype']) && $options['identity_prototype'] instanceof IdentityInterface) {
            $this->setIdentityPrototype($options['identity_prototype']);
        }

        if (isset($options['identity_hydrator']) && $options['identity_hydrator'] instanceof HydratorInterface) {
            $this->setIdentityHydrator($options['identity_hydrator']);
        }

        if (isset($options['authentication_options'])
            && $options['authentication_options'] instanceof AuthenticationOptions
        ) {
            $this->setAuthenticationOptions($options['authentication_options']);
        }

        if (!$this->identityPrototype instanceof IdentityInterface) {
            throw new InvalidArgumentException('Identity prototype is required and must be an instance of ' .
                IdentityInterface::class);
        }
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return AuthenticationOptions
     */
    public function getAuthenticationOptions(): AuthenticationOptions
    {
        if (!$this->authenticationOptions) {
            $this->authenticationOptions = new AuthenticationOptions([]);
        }
        return $this->authenticationOptions;
    }

    /**
     * @param AuthenticationOptions $authenticationOptions
     */
    public function setAuthenticationOptions(AuthenticationOptions $authenticationOptions)
    {
        $this->authenticationOptions = $authenticationOptions;
    }

    /**
     * @param array $identity
     * @return IdentityInterface
     */
    protected function hydrateIdentity(array $identity): IdentityInterface
    {
        $identity = $this->getIdentityHydrator()->hydrate($identity, $this->getIdentityPrototype());
        if (!$identity instanceof IdentityInterface) {
            throw new RuntimeException(sprintf(
                'Identity object must be an instance of %s, "%s given"',
                IdentityInterface::class,
                is_object($identity) ? get_class($identity) : gettype($identity)
            ));
        }

        return $identity;
    }

    /**
     * @return HydratorInterface
     */
    public function getIdentityHydrator(): HydratorInterface
    {
        if (!$this->identityHydrator instanceof HydratorInterface) {
            $this->identityHydrator = new ClassMethodsHydrator(false);
        }
        return $this->identityHydrator;
    }

    /**
     * @param HydratorInterface $identityHydrator
     */
    public function setIdentityHydrator(HydratorInterface $identityHydrator)
    {
        $this->identityHydrator = $identityHydrator;
    }

    /**
     * @return IdentityInterface
     */
    public function getIdentityPrototype(): IdentityInterface
    {
        return $this->identityPrototype;
    }

    /**
     * @param IdentityInterface $identityPrototype
     */
    public function setIdentityPrototype(IdentityInterface $identityPrototype)
    {
        $this->identityPrototype = $identityPrototype;
    }
}
