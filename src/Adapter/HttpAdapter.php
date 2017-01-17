<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Adapter;

use Dot\Authentication\AuthenticationResult;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Identity\IdentityInterface;
use Dot\Authentication\Options\AuthenticationOptions;
use Dot\Authentication\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\Adapter\Http;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Result;
use Zend\Http\Response as HttpResponse;
use Zend\Psr7Bridge\Psr7Response;
use Zend\Psr7Bridge\Psr7ServerRequest;

/**
 * Class HttpAdapter
 * @package Dot\Authentication\Adapter
 */
class HttpAdapter extends AbstractAdapter
{
    /** @var Http */
    protected $zendHttpAdapter;

    /** @var  AuthenticationOptions */
    protected $options;

    /**
     * HttpAdapter constructor.
     * @param AuthenticationOptions $options
     * @param array $config
     * @param ResolverInterface|null $basicResolver
     * @param ResolverInterface|null $digestResolver
     */
    public function __construct(
        AuthenticationOptions $options,
        array $config,
        ResolverInterface $basicResolver = null,
        ResolverInterface $digestResolver = null
    ) {
        $this->options = $options;
        $this->zendHttpAdapter = new Http($config);

        if ($basicResolver) {
            $this->zendHttpAdapter->setBasicResolver($basicResolver);
        }

        if ($digestResolver) {
            $this->zendHttpAdapter->setDigestResolver($digestResolver);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    public function prepare(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;

        //convert from psr7 to zend-http
        $zfRequest = Psr7ServerRequest::toZend($request);
        $zfResponse = Psr7Response::toZend($response);

        $this->zendHttpAdapter->setRequest($zfRequest);
        $this->zendHttpAdapter->setResponse($zfResponse);
    }

    /**
     * @return Http
     */
    public function getZendHttpAdapter()
    {
        return $this->zendHttpAdapter;
    }

    /**
     * @return ResponseInterface
     */
    public function challenge()
    {
        $this->zendHttpAdapter->challengeClient();
        $response = Psr7Response::fromZend($this->zendHttpAdapter->getResponse());

        return $response;
    }

    /**
     * @return bool|AuthenticationResult
     * @throws \Exception
     */
    public function authenticate()
    {
        //return null if no auth info provided, consider guest
        if ($this->request &&
            !$this->request->hasHeader('Authorization') &&
            !$this->request->hasHeader('Proxy-Authorization')
        ) {
            return false;
        }

        $result = $this->zendHttpAdapter->authenticate();
        return $this->marshalZendResult($result);
    }

    /**
     * @param Result $result
     * @return AuthenticationResult
     *
     * @throws \Exception
     */
    protected function marshalZendResult(Result $result)
    {
        //get the zf2 messages and extract info into the psr7 messages
        $zfResponse = $this->zendHttpAdapter->getResponse();
        $zfRequest = $this->zendHttpAdapter->getRequest();

        $this->request = Psr7ServerRequest::fromZend($zfRequest);
        $this->response = Psr7Response::fromZend($zfResponse);

        $code = Utils::$authResultCodeMap[$result->getCode()];
        //we'll give the user only general error info, to prevent user enumeration attacks
        $message = $this->options->getMessagesOptions()->getMessage($code);
        $identity = null;

        if ($result->isValid()) {
            $identity = $result->getIdentity();
            //try to convert to array if not already...
            $identity = (array)$identity;
            if (empty($identity)) {
                throw new RuntimeException("Missing identity object or cannot be converted to array");
            }

            if ($this->identityPrototype && $this->identityHydrator) {
                $identity = $this->identityHydrator->hydrate($identity, $this->identityPrototype);
                if (!$identity instanceof IdentityInterface) {
                    throw new RuntimeException(sprintf(
                        'Identity must be an instance of %s, "%s given"',
                        IdentityInterface::class,
                        is_object($identity) ? get_class($identity) : gettype($identity)
                    ));
                }
            } else {
                throw new RuntimeException("Missing required identity prototype and/or identity hydrator");
            }
        }

        return new AuthenticationResult($code, $this->request, $this->response, $identity, $message);
    }
}
