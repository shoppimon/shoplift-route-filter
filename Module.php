<?php

/**
 * Shoplift\RouteFilter - Easy per-route input filtering for ZF2 / Apigility apps
 *
 * @copyright (c) 2015 Shoppimon LTD
 * @author    Shahar Evron, shahar@shoppimon.com
 */

namespace Shoplift\RouteFilter;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $services = $app->getServiceManager();
        $events   = $app->getEventManager();
        $listener = $services->get('Shoplift\RouteFilter\RouteListener'); /* */
        $events->attach(MvcEvent::EVENT_ROUTE, $listener, -1000);
    }
}
