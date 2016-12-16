<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Factory;

use Dot\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Dot\Authentication\Exception\RuntimeException;
use Dot\Authentication\Options\AuthenticationOptions;
use Dot\Helpers\DependencyHelperTrait;
use Interop\Container\ContainerInterface;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;

/**
 * Class CallbackCheckAdapterFactory
 * @package Dot\Authentication\Factory
 */
class CallbackCheckAdapterFactory
{
    use DependencyHelperTrait;

    /**
     * @param ContainerInterface $container
     * @param $resolvedName
     * @param array $options
     * @return CallbackCheckAdapter
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $resolvedName, array $options = [])
    {
        /** @var AuthenticationOptions $moduleOptions */
        $moduleOptions = $container->get(AuthenticationOptions::class);

        //get identity and its hydrator objects, as set in config
        $identity = $this->getDependencyObject($container, $moduleOptions->getIdentityClass());
        if(!is_object($identity)) {
            throw new RuntimeException('No valid identity prototype specified');
        }
        $hydrator = $this->getDependencyObject($container, $moduleOptions->getIdentityHydratorClass());
        if(!$hydrator instanceof HydratorInterface) {
            $hydrator = new ClassMethods(false);
        }

        $dbAdapter = isset($options['db_adapter']) ? $options['db_adapter'] : '';
        $tableName = isset($options['table_name']) ? $options['table_name'] : '';

        $identityColumns = isset($options['identity_columns']) ? $options['identity_columns'] : [];
        if (is_string($identityColumns)) {
            $identityColumns = array($identityColumns);
        }

        if (!is_array($identityColumns)) {
            throw new RuntimeException("CallbackCheck adapter identity columns must be a string or an array of strings");
        }

        $credentialColumn = isset($options['credential_column']) ? $options['credential_column'] : null;
        if ($credentialColumn && !is_string($credentialColumn)) {
            throw new RuntimeException("CallbackCheck adapter credential column must be a string");
        }

        $callbackCheck = isset($options['callback_check']) ? $options['callback_check'] : null;

        if (empty($dbAdapter) || !is_string($dbAdapter) || !$container->has($dbAdapter)) {
            throw new RuntimeException(sprintf("CallbackCheck adapter needs a zend db adapter name option"));
        }

        if (empty($tableName) || !is_string($tableName)) {
            throw new RuntimeException(sprintf("CallbackCheck adapter missing table name option"));
        }

        $callbackCheck = $this->getDependencyObject($container, $callbackCheck);
        if ($callbackCheck && !is_callable($callbackCheck)) {
            throw new RuntimeException("CallbackCheck adapter needs a valid callable as the credential check method");
        }

        $db = $container->get($dbAdapter);

        $adapter = new CallbackCheckAdapter(
            $moduleOptions, $db, $tableName, $identityColumns, $credentialColumn, $callbackCheck);

        $adapter->setIdentityPrototype($identity);
        $adapter->setIdentityHydrator($hydrator);

        return $adapter;
    }
}