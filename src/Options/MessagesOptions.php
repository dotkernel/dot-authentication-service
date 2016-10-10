<?php
/**
 * Created by PhpStorm.
 * User: n3vrax
 * Date: 10/10/2016
 * Time: 7:01 PM
 */

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
        AuthenticationResult::SUCCESS => 'Welcome, you authenticated successfully'
    ];

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = ArrayUtils::merge($this->messages, $messages);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getMessage($key)
    {
        return isset($this->messages[$key]) ? $this->messages[$key] : null;
    }
}