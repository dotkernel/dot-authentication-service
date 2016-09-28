<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Adapter\DbTable;

/**
 * Class DbCredentials
 * @package Dot\Authentication\Adapter\DbTable
 */
class DbCredentials
{
    /** @var  string */
    protected $identity;

    /** @var string */
    protected $identityColumn;

    /** @var  string */
    protected $credential;

    /** @var string */
    protected $credentialColumn;

    /**
     * DbCredentials constructor.
     * @param string $identity
     * @param string $credential
     * @param null|string $identityColumn
     * @param null|string $credentialColumn
     */
    public function __construct(
        $identity,
        $credential,
        $identityColumn = null,
        $credentialColumn = null
    )
    {
        $this->identity = $identity;
        $this->identityColumn = $identityColumn;
        $this->credential = $credential;
        $this->credentialColumn = $credentialColumn;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return null|string
     */
    public function getIdentityColumn()
    {
        return $this->identityColumn;
    }

    /**
     * @return string
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * @return null|string
     */
    public function getCredentialColumn()
    {
        return $this->credentialColumn;
    }
}