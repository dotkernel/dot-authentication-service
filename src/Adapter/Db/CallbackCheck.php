<?php
/**
 * @copyright: DotKernel
 * @library: dot-authentication-service
 * @author: n3vrax
 * Date: 1/28/2017
 * Time: 2:09 AM
 */

namespace Dot\Authentication\Adapter\Db;

use Dot\Authentication\Adapter\AbstractAdapter;
use Dot\Authentication\AuthenticationResult;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Zend\Authentication\Result;
use Zend\Db\Adapter\Adapter;

/**
 * Class CallbackCheck
 * @package Dot\Authentication\Adapter\Db
 */
class CallbackCheck extends AbstractAdapter
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

    /** @var  CallbackCheckAdapter */
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

        $this->zendCallbackAdapter = new CallbackCheckAdapter(
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
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function prepare(ServerRequestInterface $request, ResponseInterface $response) : void
    {
        $this->request = $request;
        $this->response = $response;

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
        return $this->response->withStatus(401)
            ->withHeader('WWW-Authenticate', 'FormBased');
    }

    /**
     * @return bool
     */
    public function authenticate() : AuthenticationResult
    {
        $result = null;
        if (! $this->getCredentials()) {
            return new AuthenticationResult();
        }

        $credentials = $this->getCredentials();
        $identityColumns = $this->getIdentityColumns();
        $credentialColumn = $this->getCredentialColumn();

        //add identity column to check
        if ($credentials->getIdentityColumn() !== null
            && !in_array($credentials->getIdentityColumn(), $identityColumns)
        ) {
            $identityColumns = array_unshift($identityColumns, $credentials->getIdentityColumn());
        }
        //if passed credentials contain a credential column, overwrite the config one
        if ($credentials->getCredentialColumn() !== null) {
            $credentialColumn = $credentials->getCredentialColumn();
        }

        if (empty($identityColumns) || ! $credentialColumn) {
            throw new RuntimeException(
                "CallbackCheck adapter requires at least one identity column name and credential column name"
            );
        }

        //go over the identities and stop if one is found
        foreach ($identityColumns as $identityColumn) {
            if (!is_string($identityColumn) || empty($identityColumn) || !is_string($credentialColumn)) {
                continue;
            }

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
    }

    /**
     * @param Result $result
     * @return AuthenticationResult
     * @throws \Exception
     */
    protected function marshalZendResult(Result $result)
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

            if ($this->identityPrototype && $this->identityHydrator) {
                $identity = $this->identityHydrator->hydrate((array)$identity, $this->identityPrototype);
                if (!$identity instanceof IdentityInterface) {
                    throw new RuntimeException(sprintf(
                        'Identity must be an instance of %s, "%s given"',
                        IdentityInterface::class,
                        is_object($identity) ? get_class($identity) : gettype($identity)
                    ));
                }
            } else {
                throw new RuntimeException("Missing required identity prototype and/or identity hydrator");
            }
        } else {
            //change response to a 401 Unauthorized
            //will use a custom www-authenticate header value for this one
            //usually this adapter will be used for form based auth, we dont want the browser to challenge client
            $this->response = $this->response->withStatus(401)
                ->withHeader('WWW-Authenticate', 'FormBased');
        }

        return new AuthenticationResult($code, $this->request, $this->response, $identity, $message);
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
     * @return CallbackCheck
     */
    public function setAdapter(Adapter $adapter): CallbackCheck
    {
        $this->adapter = $adapter;
        return $this;
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
     * @return CallbackCheck
     */
    public function setTable(string $table): CallbackCheck
    {
        $this->table = $table;
        return $this;
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
     * @return CallbackCheck
     */
    public function setIdentityColumns(array $identityColumns): CallbackCheck
    {
        $this->identityColumns = $identityColumns;
        return $this;
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
     * @return CallbackCheck
     */
    public function setCredentialColumn(string $credentialColumn): CallbackCheck
    {
        $this->credentialColumn = $credentialColumn;
        return $this;
    }

    /**
     * @return callable
     */
    public function getCallbackCheck(): callable
    {
        return $this->callbackCheck;
    }

    /**
     * @param callable $callbackCheck
     * @return CallbackCheck
     */
    public function setCallbackCheck(callable $callbackCheck): CallbackCheck
    {
        $this->callbackCheck = $callbackCheck;
        return $this;
    }

    /**
     * @return CallbackCheckAdapter
     */
    public function getZendCallbackAdapter(): CallbackCheckAdapter
    {
        return $this->zendCallbackAdapter;
    }

    /**
     * @param CallbackCheckAdapter $zendCallbackAdapter
     * @return CallbackCheck
     */
    public function setZendCallbackAdapter(CallbackCheckAdapter $zendCallbackAdapter): CallbackCheck
    {
        $this->zendCallbackAdapter = $zendCallbackAdapter;
        return $this;
    }

    /**
     * @return DbCredentials
     */
    public function getCredentials(): DbCredentials
    {
        return $this->credentials;
    }

    /**
     * @param DbCredentials $credentials
     * @return CallbackCheck
     */
    public function setCredentials(DbCredentials $credentials): CallbackCheck
    {
        $this->credentials = $credentials;
        return $this;
    }
}
