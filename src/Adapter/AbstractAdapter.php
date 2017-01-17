<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Adapter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Hydrator\HydratorInterface;

/**
 * Class AbstractAdapter
 * @package Dot\Authentication\Adapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /** @var  ServerRequestInterface */
    protected $request;

    /** @var  ResponseInterface */
    protected $response;

    /** @var  object */
    protected $identityPrototype;

    /** @var  HydratorInterface */
    protected $identityHydrator;

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     * @return AbstractAdapter
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     * @return AbstractAdapter
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return object
     */
    public function getIdentityPrototype()
    {
        return $this->identityPrototype;
    }

    /**
     * @param object $identityPrototype
     * @return AbstractAdapter
     */
    public function setIdentityPrototype($identityPrototype)
    {
        $this->identityPrototype = $identityPrototype;
        return $this;
    }

    /**
     * @return HydratorInterface
     */
    public function getIdentityHydrator()
    {
        return $this->identityHydrator;
    }

    /**
     * @param HydratorInterface $identityHydrator
     * @return AbstractAdapter
     */
    public function setIdentityHydrator($identityHydrator)
    {
        $this->identityHydrator = $identityHydrator;
        return $this;
    }
}
