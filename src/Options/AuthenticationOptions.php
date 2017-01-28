<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

declare(strict_types=1);

namespace Dot\Authentication\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class AuthenticationOptions
 * @package Dot\Authentication\Options
 */
class AuthenticationOptions extends AbstractOptions
{
    /** @var  array */
    protected $adapter;

    /** @var  array */
    protected $storage;

    /** @var  string */
    protected $identityPrototype;

    /** @var  string */
    protected $identityHydrator;

    /** @var  MessagesOptions */
    protected $messagesOptions;

    /**
     * AuthenticationOptions constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        $this->__strictMode__ = false;
        parent::__construct($options);
    }

    /**
     * @return array
     */
    public function getAdapter() : array
    {
        return $this->adapter;
    }

    /**
     * @param array $adapter
     */
    public function setAdapter(array $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return array
     */
    public function getStorage() : array
    {
        return $this->storage;
    }

    /**
     * @param array $storage
     */
    public function setStorage(array $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getIdentityPrototype() : string
    {
        return $this->identityPrototype;
    }

    /**
     * @param string $identityPrototype
     */
    public function setIdentityPrototype(string $identityPrototype)
    {
        $this->identityPrototype = $identityPrototype;
    }

    /**
     * @return string
     */
    public function getIdentityHydrator() : string
    {
        return $this->identityHydrator;
    }

    /**
     * @param string $identityHydrator
     */
    public function setIdentityHydrator(string $identityHydrator)
    {
        $this->identityHydrator = $identityHydrator;
    }

    /**
     * @return MessagesOptions
     */
    public function getMessagesOptions() : MessagesOptions
    {
        if (!$this->messagesOptions) {
            $this->setMessagesOptions([]);
        }
        return $this->messagesOptions;
    }

    /**
     * @param array $messagesOptions
     */
    public function setMessagesOptions(array $messagesOptions)
    {
        $this->messagesOptions = new MessagesOptions($messagesOptions);
    }
}
