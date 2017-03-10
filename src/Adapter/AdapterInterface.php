<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Adapter;

use Dot\Authentication\AuthenticationResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface AdapterInterface
 * @package Dot\Authentication\Adapter
 */
interface AdapterInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    public function prepare(ServerRequestInterface $request);

    /**
     * @return AuthenticationResult
     */
    public function authenticate(): AuthenticationResult;

    /**
     * @return ResponseInterface
     */
    public function challenge(): ResponseInterface;
}
