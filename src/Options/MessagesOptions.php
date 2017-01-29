<?php
/**
 * Created by PhpStorm.
 * User: n3vrax
 * Date: 10/10/2016
 * Time: 7:01 PM
 */

declare(strict_types = 1);

namespace Dot\Authentication\Options;

use Dot\Authentication\AuthenticationResult;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\ArrayUtils;

/**
 * Class MessageOptions
 * @package Dot\Authentication\Options
 */
class MessagesOptions extends AbstractOptions
{
    /** @var array */
    protected $messages = [
        AuthenticationResult::FAILURE => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_INVALID_CREDENTIALS => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_UNCATEGORIZED => 'Authentication failure. Check your credentials',
        AuthenticationResult::FAILURE_MISSING_CREDENTIALS => 'Authentication failure. Missing credentials',
        AuthenticationResult::SUCCESS => 'Welcome, you authenticated successfully'
    ];

    public function __construct($options = null)
    {
        $this->__strictMode__ = false;
        parent::__construct($options);
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = ArrayUtils::merge($this->messages, $messages);
    }

    /**
     * @param int $key
     * @return string
     */
    public function getMessage(int $key): string
    {
        return isset($this->messages[$key]) ? $this->messages[$key] : '';
    }
}
