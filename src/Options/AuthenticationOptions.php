<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

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
    public function getAdapter(): array
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
    public function getStorage(): array
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
    public function getIdentityPrototype(): string
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
    public function getIdentityHydrator(): string
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
}
