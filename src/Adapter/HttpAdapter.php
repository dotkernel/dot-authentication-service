<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Adapter;

use Dot\Authentication\AuthenticationResult;
use Dot\Authentication\Exception\InvalidArgumentException;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Authentication\Adapter\Http;
use Laminas\Authentication\Adapter\Http\ResolverInterface;
use Laminas\Authentication\Result;
use Laminas\Diactoros\Response;
use Laminas\Http\Response as HttpResponse;
use Laminas\Psr7Bridge\Psr7Response;
use Laminas\Psr7Bridge\Psr7ServerRequest;

/**
 * Class HttpAdapter
 * @package Dot\Authentication\Adapter
 */
class HttpAdapter extends AbstractAdapter
{
    /** @var Http */
    protected $laminasHttpAdapter;

    /** @var  array */
    protected $config;

    /** @var  ResolverInterface */
    protected $basicResolver;

    /** @var  ResolverInterface */
    protected $digestResolver;

    /**
     * HttpAdapter constructor.
     * @param array $options
     *
     * Valid options are:
     * - identity_prototype : identity class which will be hydrated
     * - identity_hydrator : instance of HydratorInterface used to hydrate the identity object
     * - config : config array as required by the underlying laminas Http adapter
     * - basic_resolver : ResolverInterface instance to use for basic http authentication
     * - digest_resolver : ResolverInterface instance to use for digest http authentication
     */
    public function __construct(array $options = null)
    {
        $options = $options ?? [];
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

        $this->laminasHttpAdapter = new Http($this->getConfig());

        if ($this->getBasicResolver()) {
            $this->laminasHttpAdapter->setBasicResolver($this->getBasicResolver());
        }

        if ($this->getDigestResolver()) {
            $this->laminasHttpAdapter->setDigestResolver($this->getDigestResolver());
        }
    }

    protected function validate()
    {
        if (empty($this->getConfig())) {
            throw new InvalidArgumentException('Http adapter config not provided');
        }

        if (!$this->getBasicResolver() && !$this->getDigestResolver()) {
            throw new InvalidArgumentException('At least one http resolver must be provided');
        }
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
     * @return ResolverInterface
     */
    public function getBasicResolver(): ?ResolverInterface
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
    public function getDigestResolver(): ?ResolverInterface
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

    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    public function prepare(ServerRequestInterface $request)
    {
        $this->setRequest($request);

        //convert from psr7 to laminas-http
        $zfRequest = Psr7ServerRequest::toLaminas($request);
        $zfResponse = Psr7Response::toLaminas(new Response());

        $this->laminasHttpAdapter->setRequest($zfRequest);
        $this->laminasHttpAdapter->setResponse($zfResponse);
    }

    /**
     * @return ResponseInterface
     */
    public function challenge(): ResponseInterface
    {
        $this->laminasHttpAdapter->challengeClient();
        $response = Psr7Response::fromLaminas($this->laminasHttpAdapter->getResponse());

        return $response;
    }

    /**
     * @return AuthenticationResult
     * @throws \Exception
     */
    public function authenticate(): AuthenticationResult
    {
        //return null if no auth info provided, consider guest
        if ($this->request &&
            !$this->request->hasHeader('Authorization') &&
            !$this->request->hasHeader('Proxy-Authorization')
        ) {
            return new AuthenticationResult(
                AuthenticationResult::FAILURE_MISSING_CREDENTIALS,
                Utils::$authCodeToMessage[AuthenticationResult::FAILURE_MISSING_CREDENTIALS]
            );
        }

        $result = $this->laminasHttpAdapter->authenticate();
        if ($result) {
            return $this->marshalLaminasResult($result);
        }

        return new AuthenticationResult(
            AuthenticationResult::FAILURE_UNCATEGORIZED,
            Utils::$authCodeToMessage[AuthenticationResult::FAILURE_UNCATEGORIZED]
        );
    }

    /**
     * @param Result $result
     * @return AuthenticationResult
     *
     * @throws \Exception
     */
    protected function marshalLaminasResult(Result $result): AuthenticationResult
    {
        $code = Utils::$authResultCodeMap[$result->getCode()];
        //we'll give the user only general error info, to prevent user enumeration attacks
        $message = Utils::$authCodeToMessage[$code];

        $identity = null;
        if ($result->isValid()) {
            $identity = $result->getIdentity();
            //try to convert to array if not already...
            $identity = (array)$identity;
            if (empty($identity)) {
                throw new RuntimeException("Missing identity object or cannot be converted to array");
            }

            $identity = $this->hydrateIdentity($identity);
        }

        return new AuthenticationResult($code, $message, $identity);
    }

    /**
     * @return Http
     */
    public function getLaminasHttpAdapter(): Http
    {
        return $this->laminasHttpAdapter;
    }

    /**
     * @param Http $laminasHttpAdapter
     */
    public function setLaminasHttpAdapter(Http $laminasHttpAdapter)
    {
        $this->laminasHttpAdapter = $laminasHttpAdapter;
    }
}
