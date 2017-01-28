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
    public function prepare(ServerRequestInterface $request) : void;

    /**
     * @return AuthenticationResult
     */
    public function authenticate() : AuthenticationResult;

    /**
     * @return ResponseInterface
     */
    public function challenge() : ResponseInterface;
}
