<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Adapter\DbTable;

use Dot\Authentication\Adapter\AbstractAdapter;
use Dot\Authentication\AuthenticationResult;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Identity\IdentityInterface;
use Dot\Authentication\Options\AuthenticationOptions;
use Dot\Authentication\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\Result;
use Zend\Db\Adapter\Adapter;

/**
 * Class CallbackCheckAdapter
 * @package Dot\Authentication\Adapter\DbTable
 */
class CallbackCheckAdapter extends AbstractAdapter
{
    /** @var \Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter */
    protected $zendCallbackAdapter;

    /** @var  DbCredentials */
    protected $credentials;

    /** @var array */
    protected $identityColumns;

    /** @var null|string */
    protected $credentialColumn;

    /** @var  AuthenticationOptions */
    protected $options;

    /**
     * CallbackCheckAdapter constructor.
     * @param AuthenticationOptions $options
     * @param Adapter $zendDbAdapter
     * @param $tableName
     * @param array $identityColumns
     * @param null $credentialColumn
     * @param callable|null $credentialValidationCallback
     */
    public function __construct(
        AuthenticationOptions $options,
        Adapter $zendDbAdapter,
        $tableName,
        array $identityColumns = [],
        $credentialColumn = null,
        callable $credentialValidationCallback = null
    ) {
        //set properties
        $this->options = $options;
        $this->identityColumns = $identityColumns;
        $this->credentialColumn = $credentialColumn;

        //take the first identity column as default
        $identityColumn = null;
        if (!empty($identityColumns)) {
            $identityColumn = $identityColumns[0];
        }

        //instantiate zend callback check adapter
        $this->zendCallbackAdapter = new \Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter(
            $zendDbAdapter,
            $tableName,
            $identityColumn,
            $credentialColumn,
            $credentialValidationCallback
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function prepare(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->credentials = $request->getAttribute(DbCredentials::class, null);
        if ($this->credentials && !$this->credentials instanceof DbCredentials) {
            throw new RuntimeException(
                sprintf(
                    "CallbackCheck adapter needs credentials to be provided as an instance of %s",
                    DbCredentials::class
                )
            );
        }
    }

    public function challenge()
    {
        return $this->response->withStatus(401)
            ->withHeader('WWW-Authenticate', 'FormBased');
    }

    /**
     * @return bool|AuthenticationResult
     * @throws \Exception
     */
    public function authenticate()
    {
        $result = null;
        if ($this->credentials) {
            $identityColumns = $this->identityColumns;
            $credentialColumn = $this->credentialColumn;

            //add identity column to check
            if ($this->credentials->getIdentityColumn() !== null
                && !in_array($this->credentials->getIdentityColumn(), $identityColumns)
            ) {
                $identityColumns = array_unshift($identityColumns, $this->credentials->getIdentityColumn());
            }
            //if passed credentials contain a credential column, overwrite the config one
            if ($this->credentials->getCredentialColumn() !== null) {
                $credentialColumn = $this->credentials->getCredentialColumn();
            }

            if (empty($identityColumns) || $credentialColumn === null) {
                throw new RuntimeException(
                    "CallbackCheck adapter requires at least one identity column name and credential column"
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

        return false;
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
        $message = $this->options->getMessagesOptions()->getMessage($code);
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
}
