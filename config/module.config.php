<?php

/**
 * Shoplift\RouteFilter - Easy per-route input filtering for ZF2 / Apigility apps
 *
 * @copyright (c) 2015 Shoppimon LTD
 */

return array(
    'service_manager' => array(
        'invokables' => array(
            'Shoplift\RouteFilter\RouteListener' => 'Shoplift\RouteFilter\RouteListener'
        )
    ),
    'shoplift-route-filters' => array()
);
