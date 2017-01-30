<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication;

use Zend\Authentication\Result;

/**
 * Class Utils
 * @package Dot\Authentication
 */
final class Utils
{
    /** @var array */
    public static $authResultCodeMap = [
        Result::FAILURE => AuthenticationResult::FAILURE,
        Result::FAILURE_CREDENTIAL_INVALID => AuthenticationResult::FAILURE_INVALID_CREDENTIALS,
        Result::FAILURE_IDENTITY_AMBIGUOUS => AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS,
        Result::FAILURE_IDENTITY_NOT_FOUND => AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND,
        Result::FAILURE_UNCATEGORIZED => AuthenticationResult::FAILURE_UNCATEGORIZED,
        Result::SUCCESS => AuthenticationResult::SUCCESS
    ];

    public static $authCodeToMessage = [
        AuthenticationResult::FAILURE => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_INVALID_CREDENTIALS => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_UNCATEGORIZED => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_MISSING_CREDENTIALS => 'Authentication failure. Missing credentials',
        AuthenticationResult::SUCCESS => 'Welcome, you authenticated successfully'
    ];
}
