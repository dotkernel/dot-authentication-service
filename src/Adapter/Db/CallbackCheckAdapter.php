<?php
/**
 * @copyright: DotKernel
 * @library: dot-authentication-service
 * @author: n3vrax
 * Date: 1/28/2017
 * Time: 2:09 AM
 */

declare(strict_types=1);

namespace Dot\Authentication\Adapter\Db;

use Dot\Authentication\Adapter\AbstractAdapter;
use Dot\Authentication\AuthenticationResult;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\Result;
use Zend\Db\Adapter\Adapter;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter as ZendCallbackCheckAdapter;

/**
 * Class CallbackCheck
 * @package Dot\Authentication\Adapter\Db
 */
class CallbackCheckAdapter extends AbstractAdapter
{
    /** @var  Adapter */
    protected $adapter;

    /** @var  string */
    protected $table;

    /** @var  array */
    protected $identityColumns = ['username', 'email'];

    /** @var  string */
    protected $credentialColumn = 'password';

    /** @var  callable */
    protected $callbackCheck;

    /** @var  ZendCallbackCheckAdapter */
    protected $zendCallbackAdapter;

    /** @var  DbCredentials */
    protected $credentials;

    /**
     * CallbackCheck constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (isset($options['adapter']) && $options['adapter'] instanceof Adapter) {
            $this->setAdapter($options['adapter']);
        }

        if (isset($options['table']) && is_string($options['table'])) {
            $this->setTable($options['table']);
        }

        if (isset($options['identity_columns'])) {
            $this->setIdentityColumns((array) $options['identity_columns']);
        }

        if (isset($options['credential_column']) && is_string($options['credential_column'])) {
            $this->setCredentialColumn($options['credential_column']);
        }

        if (isset($options['callback_check']) && is_callable($options['callback_check'])) {
            $this->setCallbackCheck($options['callback_check']);
        }

        $this->validate();

        $this->zendCallbackAdapter = new ZendCallbackCheckAdapter(
            $this->adapter,
            $this->table,
            $this->identityColumns[0],
            $this->credentialColumn,
            $this->callbackCheck
        );
    }

    protected function validate() : void
    {
        if (! $this->adapter) {
            throw new RuntimeException('Db adapter is required and must be an instance of ' . Adapter::class);
        }

        if (! $this->table) {
            throw new RuntimeException('Table is required and must be a non empty string');
        }

        if (empty($this->identityColumns)) {
            throw new RuntimeException('At least one identity column name must be specified');
        }

        if (empty($this->credentialColumn)) {
            throw new RuntimeException('Credential column must be given as a non empty string');
        }
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function prepare(ServerRequestInterface $request) : void
    {
        $this->request = $request;

        $credentials = $request->getAttribute(DbCredentials::class, null);
        if ($credentials && !$credentials instanceof DbCredentials) {
            throw new RuntimeException(
                sprintf(
                    "Adapter needs credentials to be provided as an instance of %s as a request attribute",
                    DbCredentials::class
                )
            );
        }

        $this->setCredentials($credentials);
    }

    /**
     * @return ResponseInterface
     */
    public function challenge() : ResponseInterface
    {
        return new EmptyResponse(401, ['WWW-Authenticate' => 'FormBased']);
    }

    /**
     * @return AuthenticationResult
     */
    public function authenticate() : AuthenticationResult
    {
        $result = null;
        if (! $this->getCredentials()) {
            return new AuthenticationResult(
                AuthenticationResult::FAILURE_MISSING_CREDENTIALS,
                $this->getAuthenticationOptions()->getMessagesOptions()
                    ->getMessage(AuthenticationResult::FAILURE_MISSING_CREDENTIALS)
            );
        }

        $credentials = $this->getCredentials();
        $identityColumns = $this->getIdentityColumns();
        $credentialColumn = $this->getCredentialColumn();

        //add identity column to check
        if (! empty($credentials->getIdentityColumn())
            && !in_array($credentials->getIdentityColumn(), $identityColumns)
        ) {
            $identityColumns = array_unshift($identityColumns, $credentials->getIdentityColumn());
        }
        //if passed credentials contain a credential column, overwrite the config one
        if (! empty($credentials->getCredentialColumn())) {
            $credentialColumn = $credentials->getCredentialColumn();
        }

        if (empty($identityColumns) || empty($credentialColumn)) {
            throw new RuntimeException(
                "CallbackCheck adapter requires at least one identity column name and credential column name"
            );
        }

        //go over the identities and stop if one is found
        foreach ($identityColumns as $identityColumn) {
            $this->zendCallbackAdapter->setIdentityColumn($identityColumn);
            $this->zendCallbackAdapter->setCredentialColumn($credentialColumn);

            $this->zendCallbackAdapter->setIdentity($this->credentials->getIdentity());
            $this->zendCallbackAdapter->setCredential($this->credentials->getCredential());

            //continue looping if its not valid and identity is not found
            //it will break if credentials invalid is received, as we suppose
            // the identity column was good, but credentials were wrong
            $result = $this->zendCallbackAdapter->authenticate();
            if ($result->isValid() || $result->getCode() !== Result::FAILURE_IDENTITY_NOT_FOUND) {
                break;
            }
        }

        if ($result) {
            return $this->marshalZendResult($result);
        }

        return new AuthenticationResult(
            AuthenticationResult::FAILURE_UNCATEGORIZED,
            $this->getAuthenticationOptions()->getMessagesOptions()
                ->getMessage(AuthenticationResult::FAILURE_UNCATEGORIZED)
        );
    }

    /**
     * @param Result $result
     * @return AuthenticationResult
     * @throws \Exception
     */
    protected function marshalZendResult(Result $result) : AuthenticationResult
    {
        $code = Utils::$authResultCodeMap[$result->getCode()];
        //we'll give the user only general error info, to prevent user enumeration attacks
        $message = $this->getAuthenticationOptions()->getMessagesOptions()->getMessage($code);
        $identity = null;

        if ($result->isValid()) {
            // get the identity object from the adapter,
            // as the underlying result identity does not store the entire entity
            $identity = $this->zendCallbackAdapter->getResultRowObject(null, [$this->credentialColumn]);
            //we need an array, so try to convert...
            $identity = (array)$identity;
            if (empty($identity)) {
                throw new RuntimeException("Identity object missing or could not be converted to array");
            }

            $identity = $this->hydrateIdentity($identity);
        }

        return new AuthenticationResult($code, $message, $identity);
    }

    /**
     * @return Adapter
     */
    public function getAdapter(): Adapter
    {
        return $this->adapter;
    }

    /**
     * @param Adapter $adapter
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table)
    {
        $this->table = $table;
    }

    /**
     * @return array
     */
    public function getIdentityColumns(): array
    {
        return $this->identityColumns;
    }

    /**
     * @param array $identityColumns
     */
    public function setIdentityColumns(array $identityColumns)
    {
        $this->identityColumns = $identityColumns;
    }

    /**
     * @return string
     */
    public function getCredentialColumn(): string
    {
        return $this->credentialColumn;
    }

    /**
     * @param string $credentialColumn
     */
    public function setCredentialColumn(string $credentialColumn)
    {
        $this->credentialColumn = $credentialColumn;
    }

    /**
     * @return callable
     */
    public function getCallbackCheck(): ?callable
    {
        return $this->callbackCheck;
    }

    /**
     * @param callable $callbackCheck
     */
    public function setCallbackCheck(callable $callbackCheck)
    {
        $this->callbackCheck = $callbackCheck;
    }

    /**
     * @return ZendCallbackCheckAdapter
     */
    public function getZendCallbackAdapter(): ZendCallbackCheckAdapter
    {
        return $this->zendCallbackAdapter;
    }

    /**
     * @param ZendCallbackCheckAdapter $zendCallbackAdapter
     */
    public function setZendCallbackAdapter(ZendCallbackCheckAdapter $zendCallbackAdapter)
    {
        $this->zendCallbackAdapter = $zendCallbackAdapter;
    }

    /**
     * @return DbCredentials
     */
    public function getCredentials(): ?DbCredentials
    {
        return $this->credentials;
    }

    /**
     * @param DbCredentials $credentials
     */
    public function setCredentials(DbCredentials $credentials)
    {
        $this->credentials = $credentials;
    }
}
