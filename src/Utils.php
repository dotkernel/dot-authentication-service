<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

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
