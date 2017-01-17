<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

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
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function challenge(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;


        $this->adapter->prepare($request, $response);
        $challenge = $this->adapter->challenge();

        if ($challenge && $challenge instanceof ResponseInterface) {
            //get the WWW-authenticate header if any and add it to the current response
            if ($challenge->hasHeader('WWW-Authenticate')) {
                $response = $response->withAddedHeader(
                    'WWW-Authenticate',
                    $challenge->getHeader('WWW-Authenticate')
                );
            }
        }

        //return a 401 with authentication headers as added by adapters
        return $response->withStatus(401);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return bool|AuthenticationResult|false|null
     */
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;

        if ($request->getMethod() === 'OPTIONS') {
            return false;
        }

        $this->adapter->prepare($request, $response);

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
    public function hasIdentity()
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
     * @return $this
     */
    public function setIdentity(IdentityInterface $identity)
    {
        $this->storage->write($identity);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getIdentity()
    {
        if ($this->storage->isEmpty()) {
            return null;
        }
        return $this->storage->read();
    }
}
