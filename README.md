> [!CAUTION]
> ## Security-Only Maintenance Mode
> 
> This package is considered feature-complete, and is now in **security-only** maintenance mode.

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-authentication-service)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-authentication-service)](https://github.com/dotkernel/dot-authentication-service/blob/2.0.2/LICENSE.md)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-authentication-service/2.9.2)




# dot-authentication-service

Concrete authentication service implementation of `\Dot\Authentication\AuthenticationInterface` built using [laminas-authentication](https://github.com/laminas/laminas-authentication) authentication and storage adapters. We advise you to have a quick look at how laminas authentication works, by visiting the [documentation](https://docs.laminas.dev/laminas-authentication/)



## Installation

Run the following command in your project directory
```bash
$ composer require dotkernel/dot-authentication-service
```

Add the `ConfigProvider` class to your configuration aggregate, to register the default services.

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

We also provide a convenient factory class that configures an authentication service based on configuration.
Just merge the package's `ConfigProvider` output to your application config in order to register all the required dependencies.

## Authentication adapters

Used by the authentication service to check the credentials against some backend. Can be easily exchanged with various implementations depending on the authentication type or backend type.
An `\Dot\Authentication\Adapter\AdapterInterface` is provided which must be implemented by all adapters. Also, adapters should be registered in the `\Dot\Authentication\Adapter\AdapterPluginManager` service. More on this in the writing custom adapters section.

The AdapterInterface defines the following methods
```php
public function prepare(ServerRequestInterface $request);
```
* called internally by the authentication service, just before the authentication operation, in order to give the adapter a chance to prepare itself, by extracting the credentials from the request, in its specific manner.

```php
public function authenticate(): AuthenticationResult;
```
* checks the credentials extracted from the previous step, against some backend. Should return an `\Dot\Authentication\AuthenticationResult`

```php
public function challenge(): ResponseInterface;
```
This method is optional, it depends on the authentication type if a challenge response is required. It should return a ResponseInterface with status code 401 and a WWW-Authenticate header to indicate authentication is required.

### HttpAdapter

This adapter provides HTTP basic and digest authentication. It is built around the laminas-authentication http adapter class.
It actually wraps the laminas authentication http adapter, which can be configured by sending in the $config parameter as described by the laminas-authentication official documentation.
We'll describe later how to configure an authentication service with such an adapter.

When authenticating, this adapter extracts the credentials from the request's `Authorization` header.

### CallbackCheckAdapter

Use to authenticate request against a MySQL backend. It is called so, because it accepts a callback as one of its parameters, which is called to check the password according to specific needs(md5, bcrypt etc.)
It also wraps a laminas-authentication adapter with the same name, so much of its initialization is linked to that adapter. We'll describe later how to configure such an adapter.

This adapter requires that before calling the authentication service's authenticate method, you should manually extract the credentials from your request(POST) and initialize a `DbCredentials` class with it.
It does not make assumptions on where the credentials should be in the request. It searches for a specific attribute instead, which you should add before authenticating.
After extracting the credentials into a `DbCredentials` class, set this as a $request attribute at key `DbCredentials::class`.

```php
$request = $request->withAttribute(DbCredentials::class, $dbCredentials);
```

##### Identity prototype and hydrator
Both these adapters require an identity prototype object and an optional hydrator class(ClassMethods by default)
When successfully authenticating, it will hydrate the prototype with the user data and return it inside the `AuthenticationResult`.

## Storage adapters

Used by the authentication service to store the authenticated identity in some kind of persistent storage.
All storage adapters must implement the `\Dot\Authentication\Storage\StorageInterface` interface.
This interface extends the `\Laminas\Authentication\Storage\StorageInterface`. No additional interface methods are defined for now.

Also, storage adapters should be registered in the `\Dot\Authentication\Storage\StoragePluginManager`.

### SessionStorage

It extends the `Laminas\Authentication\Storage\Session` class, providing the same functionality, with the only difference that on each write call, it will regenerate the session id.
This is to prevent session fixation attacks, we renew the session id when user sign in.
You can find more details on this storage adapter in laminas-authentication official documentation. We'll also give you an example in the following documentation on how to configure such an adapter.


### NonPersistenStorage

It extends the `Laminas\Authentication\Storage\NonPersistent` class. As its name suggests, it does not store the identity persistently. It will be lost after script finishes running.
Useful for API implementations, where authentication is stateless.

## Configuring an AuthenticationService

First of all, make sure you merge this package's `ConfigProvider` output to your application's config. This will add all necessary dependencies to your application, including the convenient factories.
Create a separate config file in your `config/autoload` directory, call it authentication.global.php for example.

##### authentication.global.php
```php
return [
    'dot_authentication' => [
        'adapter' => [
            'type' => 'CallbackCheck',
            'options' => [
                // laminas db adapter service name
                'adapter' => 'database service name',
                
                'identity_prototype' => '\You\Identity\Class\Implementing\IdentityInterface',
                'identity_hydrator' => '\Hydrator\Class\Implementing\HydratorInterface',
                
                // your user table name
                'table' => 'user table name',
                
                // what user fields should use for authentication(db fields)
                'identity_columns' => ['username', 'email'],

                // name of the password db field
                'credential_column' => 'password'
                
                // we recommend using a service name or class name instead of closures
                // the below closure is just an example, to show you the callable signature
                // 'callback_check' => function($hash_passwd, $password) {
                //    return $hash_passwd === md5($password);
                // }
            ],
        ],

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
            //define custom http authentication resolvers here
        ],
    ]
];
```

To define a http basic adapter you can define a configuration as below:
```php
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
```
