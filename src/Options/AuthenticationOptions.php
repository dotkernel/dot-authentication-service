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

    /** @var  MessageOptions */
    protected $messageOptions;

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
     * @return MessageOptions
     */
    public function getMessageOptions()
    {
        if (!$this->messageOptions) {
            $this->setMessageOptions([]);
        }
        return $this->messageOptions;
    }

    /**
     * @param MessageOptions|array $messageOptions
     * @return AuthenticationOptions
     */
    public function setMessageOptions($messageOptions)
    {
        if (is_array($messageOptions)) {
            $this->messageOptions = new MessageOptions($messageOptions);
        } elseif ($messageOptions instanceof MessageOptions) {
            $this->messageOptions = $messageOptions;
        } else {
            throw new InvalidArgumentException(sprintf(
                'MessageOptions should be an array or an %s object. %s provided.',
                MessageOptions::class,
                is_object($messageOptions) ? get_class($messageOptions) : gettype($messageOptions)
            ));
        }
        return $this;
    }

}