<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Adapter;

use Dot\Authentication\Adapter\Db\CallbackCheckAdapter;
use Dot\Authentication\Factory\CallbackCheckAdapterFactory;
use Dot\Authentication\Factory\HttpAdapterFactory;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class AdapterPluginManager
 * @package Dot\Authentication\Adapter
 */
class AdapterPluginManager extends AbstractPluginManager
{
    protected $instanceOf = AdapterInterface::class;

    //pre-registered adapters
    protected $factories = [
        CallbackCheckAdapter::class => CallbackCheckAdapterFactory::class,
        HttpAdapter::class => HttpAdapterFactory::class,
    ];

    protected $aliases = [
        'callbackcheck' => CallbackCheckAdapter::class,
        'callbackCheck' => CallbackCheckAdapter::class,
        'CallbackCheck' => CallbackCheckAdapter::class,
        'callbackcheckadapter' => CallbackCheckAdapter::class,
        'callbackCheckAdapter' => CallbackCheckAdapter::class,
        'CallbackCheckAdapter' => CallbackCheckAdapter::class,

        'http' => HttpAdapter::class,
        'Http' => HttpAdapter::class,
        'httpadapter' => HttpAdapter::class,
        'httpAdapter' => HttpAdapter::class,
        'HttpAdapter' => HttpAdapter::class,
    ];
}
