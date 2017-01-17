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
     * @param ResponseInterface $response
     * @return void
     */
    public function prepare(ServerRequestInterface $request, ResponseInterface $response);

    /**
     * @return AuthenticationResult|null|false
     */
    public function authenticate();

    /**
     * @return ResponseInterface|null|false
     */
    public function challenge();
}
