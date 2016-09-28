<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Options;

use Dot\Authentication\AuthenticationResult;
use Zend\Stdlib\AbstractOptions;

/**
 * Class AuthenticationOptions
 * @package Dot\Authentication\Options
 */
class AuthenticationOptions extends AbstractOptions
{
    /** @var  array|mixed */
    protected $adapter;

    /** @var  mixed */
    protected $storage;

    /** @var  string */
    protected $identityClass;

    /** @var  string */
    protected $identityHydratorClass;
    
    protected $__strictMode__ = false;

    /** @var array  */
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
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param mixed $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param mixed $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getIdentityClass()
    {
        return $this->identityClass;
    }

    /**
     * @param string $identityClass
     */
    public function setIdentityClass($identityClass)
    {
        $this->identityClass = $identityClass;
    }

    /**
     * @return string
     */
    public function getIdentityHydratorClass()
    {
        return $this->identityHydratorClass;
    }

    /**
     * @param string $identityHydratorClass
     */
    public function setIdentityHydratorClass($identityHydratorClass)
    {
        $this->identityHydratorClass = $identityHydratorClass;
    }

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
        $this->messages = array_merge($this->messages, $messages);
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