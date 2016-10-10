<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Options;

use Dot\Authentication\Exception\InvalidArgumentException;
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

    /** @var  MessagesOptions */
    protected $messagesOptions;

    protected $__strictMode__ = false;

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
     * @return MessagesOptions
     */
    public function getMessagesOptions()
    {
        if (!$this->messagesOptions) {
            $this->setMessagesOptions([]);
        }
        return $this->messagesOptions;
    }

    /**
     * @param MessagesOptions|array $messagesOptions
     * @return AuthenticationOptions
     */
    public function setMessagesOptions($messagesOptions)
    {
        if (is_array($messagesOptions)) {
            $this->messagesOptions = new MessagesOptions($messagesOptions);
        } elseif ($messagesOptions instanceof MessagesOptions) {
            $this->messagesOptions = $messagesOptions;
        } else {
            throw new InvalidArgumentException(sprintf(
                'MessageOptions should be an array or an %s object. %s provided.',
                MessagesOptions::class,
                is_object($messagesOptions) ? get_class($messagesOptions) : gettype($messagesOptions)
            ));
        }
        return $this;
    }

}