<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

declare(strict_types=1);

namespace Dot\Authentication\Adapter;

use Dot\Authentication\AuthenticationResult;
use Dot\Authentication\Exception\InvalidArgumentException;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Identity\IdentityInterface;
use Dot\Authentication\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\Adapter\Http;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Result;
use Zend\Diactoros\Response;
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

    /** @var  array */
    protected $config;

    /** @var  ResolverInterface */
    protected $basicResolver;

    /** @var  ResolverInterface */
    protected $digestResolver;

    /**
     * HttpAdapter constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (isset($options['config']) && is_array($options['config'])) {
            $this->setConfig($options['config']);
        }

        if (isset($options['basic_resolver']) && $options['basic_resolver'] instanceof ResolverInterface) {
            $this->setBasicResolver($options['basic_resolver']);
        }

        if (isset($options['digest_resolver']) && $options['digest_resolver'] instanceof ResolverInterface) {
            $this->setDigestResolver($options['digest_resolver']);
        }


        $this->validate();

        $this->zendHttpAdapter = new Http($this->getConfig());

        if ($this->getBasicResolver()) {
            $this->zendHttpAdapter->setBasicResolver($this->getBasicResolver());
        }

        if ($this->getDigestResolver()) {
            $this->zendHttpAdapter->setDigestResolver($this->getDigestResolver());
        }
    }

    protected function validate() : void
    {
        if (empty($this->getConfig())) {
            throw new InvalidArgumentException('Http adapter config not provided');
        }

        if (! $this->getBasicResolver() && ! $this->getDigestResolver()) {
            throw new InvalidArgumentException('At least one http resolver must be provided');
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    public function prepare(ServerRequestInterface $request) : void
    {
        $this->setRequest($request);

        //convert from psr7 to zend-http
        $zfRequest = Psr7ServerRequest::toZend($request);
        $zfResponse = Psr7Response::toZend(new Response());

        $this->zendHttpAdapter->setRequest($zfRequest);
        $this->zendHttpAdapter->setResponse($zfResponse);
    }

    /**
     * @return ResponseInterface
     */
    public function challenge() : ResponseInterface
    {
        $this->zendHttpAdapter->challengeClient();
        $response = Psr7Response::fromZend($this->zendHttpAdapter->getResponse());

        return $response;
    }

    /**
     * @return AuthenticationResult
     * @throws \Exception
     */
    public function authenticate() : AuthenticationResult
    {
        //return null if no auth info provided, consider guest
        if ($this->request &&
            !$this->request->hasHeader('Authorization') &&
            !$this->request->hasHeader('Proxy-Authorization')
        ) {
            return new AuthenticationResult(
                AuthenticationResult::FAILURE_MISSING_CREDENTIALS,
                $this->getAuthenticationOptions()->getMessagesOptions()
                    ->getMessage(AuthenticationResult::FAILURE_MISSING_CREDENTIALS)
            );
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
    protected function marshalZendResult(Result $result) : AuthenticationResult
    {
        $code = Utils::$authResultCodeMap[$result->getCode()];
        //we'll give the user only general error info, to prevent user enumeration attacks
        $message = $this->getAuthenticationOptions()->getMessagesOptions()->getMessage($code);
        $identity = null;

        if ($result->isValid()) {
            $identity = $result->getIdentity();
            //try to convert to array if not already...
            $identity = (array)$identity;
            if (empty($identity)) {
                throw new RuntimeException("Missing identity object or cannot be converted to array");
            }

            $identity = $this->getIdentityHydrator()->hydrate($identity, $this->getIdentityPrototype());
            if (!$identity instanceof IdentityInterface) {
                throw new RuntimeException(sprintf(
                    'Identity must be an instance of %s, "%s given"',
                    IdentityInterface::class,
                    is_object($identity) ? get_class($identity) : gettype($identity)
                ));
            }
        }

        return new AuthenticationResult($code, $message, $identity);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return Http
     */
    public function getZendHttpAdapter(): Http
    {
        return $this->zendHttpAdapter;
    }

    /**
     * @param Http $zendHttpAdapter
     */
    public function setZendHttpAdapter(Http $zendHttpAdapter)
    {
        $this->zendHttpAdapter = $zendHttpAdapter;
    }

    /**
     * @return ResolverInterface
     */
    public function getBasicResolver(): ResolverInterface
    {
        return $this->basicResolver;
    }

    /**
     * @param ResolverInterface $basicResolver
     */
    public function setBasicResolver(ResolverInterface $basicResolver)
    {
        $this->basicResolver = $basicResolver;
    }

    /**
     * @return ResolverInterface
     */
    public function getDigestResolver(): ResolverInterface
    {
        return $this->digestResolver;
    }

    /**
     * @param ResolverInterface $digestResolver
     */
    public function setDigestResolver(ResolverInterface $digestResolver)
    {
        $this->digestResolver = $digestResolver;
    }
}
