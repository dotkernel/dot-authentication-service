<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

namespace Dot\Authentication\Adapter;

use Dot\Authentication\Adapter\DbTable\CallbackCheckAdapter;
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
}