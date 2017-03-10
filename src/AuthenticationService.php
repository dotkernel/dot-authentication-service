<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication;

use Dot\Authentication\Adapter\AdapterInterface;
use Dot\Authentication\Identity\IdentityInterface;
use Dot\Authentication\Storage\StorageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthenticationService
 * @package Dot\Authentication
 */
class AuthenticationService implements AuthenticationInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * AuthenticationService constructor.
     * @param AdapterInterface $adapter
     * @param StorageInterface $storage
     */
    public function __construct(AdapterInterface $adapter, StorageInterface $storage)
    {
        $this->adapter = $adapter;
        $this->storage = $storage;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function challenge(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $this->adapter->prepare($request);
        $challenge = $this->adapter->challenge();

        return $challenge;
    }

    /**
     * @param ServerRequestInterface $request
     * @return AuthenticationResult
     */
    public function authenticate(ServerRequestInterface $request): AuthenticationResult
    {
        $this->request = $request;

        if ($request->getMethod() === 'OPTIONS') {
            // let it pass, return success, but with no identity
            return new AuthenticationResult(AuthenticationResult::SUCCESS);
        }

        $this->adapter->prepare($request);

        $result = $this->adapter->authenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result) {
            if (!$result instanceof AuthenticationResult) {
                throw new \RuntimeException(
                    sprintf(
                        "Authentication adapter must return an instance of %s",
                        AuthenticationResult::class
                    )
                );
            }

            if ($result->isValid()) {
                $this->setIdentity($result->getIdentity());
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function hasIdentity(): bool
    {
        return !$this->storage->isEmpty();
    }

    /**
     * @return void
     */
    public function clearIdentity()
    {
        $this->storage->clear();
    }

    /**
     * @param IdentityInterface $identity
     */
    public function setIdentity(IdentityInterface $identity)
    {
        $this->storage->write($identity);
    }

    /**
     * @return IdentityInterface
     */
    public function getIdentity(): ?IdentityInterface
    {
        return $this->storage->read();
    }
}
