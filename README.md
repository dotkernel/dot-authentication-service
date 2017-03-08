# dot-authentication-service
Concrete implementation of a [dot-authentication](https://github.com/dotkernel/dot-authentication)'s `AuthenticationInterface` based on [zend-authentication](https://github.com/zendframework/zend-authentication).
It provides and `AuthenticationService` class that can be used to authenticate requests. The underlying implementation consists of an authentication adapter that checks the credentials against some backend and a storage adapter which stores the authenticated identity in a backend or other means that survives between multiple requests(usually the session but not restricted to).

## Installation

Add the dependency to your composer.json file by running the following command in your project root dir
```bash
$ composer require dotkernel/dot-authentication-service
```

## Usage

You'll usually inject the authentication service into your classes and use its methods, as described by its interface at [dot-authentication](https://github.com/dotkernel/dot-authentication)
```php
//...

public function __construct(AuthenticationInterface $authentication) 
{
    $this->authentication = $authentication;
}

//...

public function someMethod()
{
    if($this->authentication->hasIdentity()) {
        //do something
    }
}
```

When creating the authentication service, you'll have to provide an authentication AdapterInterface and a StorageInterface.

We already provide some default implementations of authentication adapters and storage adapters, with the possibility to write your custom ones.

We also provide a convenient factory class that configures an authentication service based on a config array.
Just merge the package's `ConfigProvider` output to your application config in order to register all the required dependencies.

## Authentication adapters

Used by the authentication service to check the credentials against some backend. Can be easily exchanged with various implementation depending on the authentication type or backend type.
An `AdapterInterface` is provided which must be implemented by all adapters. Also, adapters should be registered in the `AdapterPluginManager` service. More on this in the custom adapter writing section.

The AdapterInterface defines the following methods
```php
public function prepare(ServerRequestInterface $request);
```
Called internally by the authentication service, just before the authentication operation, in order to give the adapter a chance to prepare itself, by extracting the credentials from the request, in its specific manner.

```php
public function authenticate(): AuthenticationResult;
```
Checks the credentials extracted from the previous step, against some backend. Should return an `AuthenticationResult`

```php
public function challenge(): ResponseInterface;
```
This method is not needed by all authentication implementation. It should return a ResponseInterface with status code 401 and a WWW-Authenticate header to indicate authentication is required.


### HttpAdapter

This adapter provides HTTP basic and digest authentication. It is built around the zend-authentication http adapter class.
It actually wraps the zend authentication http adapter, which can be configured by sending in the $config parameter as described by the zend-authentication official documentation.
We'll describe later how to configure an authentication service with such an adapter.

When authenticating, this adapter extracts the credentials from the request's `Authorization` header.

### CallbackCheckAdapter

Use to authenticate request against a MySQL backend. It is called so, because it accepts a callback as one of its parameters, which is called to check the password according to specific needs(md5, bcrypt etc.)
It also wraps a zend-authentication adapter with the same name, so much of it initialization is linked to that adapter. We'll describe later how to configure such an adapter.

This adapter requires that before calling the authentication service's authenticate method, you should manually extract the credentials from your request and initialize a `DbCredentials` class with it.
It does not make assumptions on where the credentials should be in the request. It searches for a specific attribute instead, which you should add before authenticating.
After extracting the credentials into a `DbCredentials` class, set this as a $request attribute at key `DbCredentials::class`.
 
##### Identity prototype and hydrator
Both these adapters require an identity prototype object and an optional hydrator class(ClassMethods by default)
When successfully authenticating, it will hydrate the prototype with the user data and return it inside an `AuthenticationResult`.


## Storage adapters

Use by the authentication service to store the authenticated identity in some kind of persistent storage.
All storage adapters must implement the `StorageInterface` interface. Also, storage adapters should be registered in the `StoragePluginManager`.
This interface extends the `\Zend\Authentication\Storage\StorageInterface`. No additional interface methods are defined for now.

### SessionStorage

It extends the `Zend\Authentication\Storage\Session` class, providing the same functionality, with the only difference that on each write call, it will regenerate the session id.
This is to prevent session fixation attack, we renew the session id when user sign in.
You can find more details on this storage adapter in zend-authentication official documentation. We'll also give you an example in the following documentation on how to configure such an adapter.


### NonPersistenStorage

It extends the `Zend\Authentication\Storage\NonPersistent` class. As its name suggests, it does not store the identity persistently. It will be lost after script finishes running.
Useful for API implementations, where authentication is stateless.

## Configuring an AuthenticationService

First of all, make sure you merge this package's `ConfigProvider` output to your application's config. This will add all necessary dependencies to your application, including the convenient factories.
Create a separate config file in your `config/autoload` directory, call it authentication.global.php for example.

##### authentication.global.php
```php
return [
    'dot_authentication' => [
        //required by the auth adapters, it may be optional for your custom adapters
        //specify the identity entity to use and its hydrator

        //this is adapter specific
        //currently we support HTTP basic and digest
        //below is config template for callbackcheck adapter
        'adapter' => [
            'type' => 'CallbackCheck',
            'options' => [
                // zend db adapter service name
                'adapter' => 'database service name',
                
                'identity_prototype' => '\You\Identity\Class\Implementing\IdentityInterface',
                'identity_hydrator' => 'Hydrator\Class\Implementing\HydratorInterface',
                
                // your user table name
                'table' => 'user table name',
                
                // what user fields should use for authentication(db fields)
                'identity_columns' => ['username', 'email'],

                // name of the password db field
                'credential_column' => 'password'
                
                // your password checking callback, use a closure, a service name of a callable or a callable class name
                // we recommend using a service name or class name instead of closures, to be able to cache the config
                // the below closure is just an example, to show you the callable signature
                // 'callback_check' => function($hash_passwd, $password) {
                //    return $hash_passwd === md5($password);
                // }
            ],
        ],
        //this is a HTTP basic adapter config example
        /*
        'adapter' => [
            'type' => 'Http',
            'options' => [
                'identity_prototype' => '\You\Identity\Class\Implementing\IdentityInterface',
                'identity_hydrator' => 'Hydrator\Class\Implementing\HydratorInterface',
                
                'config' => [
                    'accept_schemes' => 'basic',
                    'realm' => 'api',
                ],
                
                'basic_resolver' => [
                    'name' => 'FileResolver',
                    'options' => [
                        'path' => 'path/to/.httpasswd',
                    ],
                ],
                
                'digest_resolver' => [],
            ],
        ],
        */

        //storage specific options, example below, for session storage
        'storage' => [
            'type' => 'Session',
            'options' => [
                //session namespace
                'namespace' => 'dot_auth',
                
                //what session member to use
                'member' => 'storage'
            ],
        ],

        'adapter_manager' => [
            //register custom adapters here, like you would do in a normal container
        ],
        
        'storage_manager' => [
            //register custom storage adapters
        ],
        
        'resolver_manager' => [
            //define custom http authentication resolvers here, through the resolver plugin manager
        ],
    ]
];
```
