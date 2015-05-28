ShopliftRouteFilter
===================
ShopliftRouteFilter provides config-based input filtering and validation for
query parameters and route parameters in ZF2 / Apigility apps.

It was designed to be an easy drop-in module allowing you to minimize the
amount of filtering and validation you do in controllers / Resource classes,
and follows existing Apigility content validation patterns as closely as
possible.


Installation
------------
ShopliftRouteFilter is most easily installed using
[Composer](http://getcomposer.org). You can include ShopliftRouteFilter in your
project by running the following line in your project root:

    ./composer.phar require shoppimon/shoplift-route-filter

(the path to `composer.phar` which may also simply be called `composer` may
vary depending on your system).

Another option is to manually edit your composer.json and add it to the
`"require"` section:

    "require": {
        "shoppimon/shoplift-route-filter": "*"
    }

And then, running `composer install` in your project's root directory.


Configuration & Usage
---------------------
Once you have ShopliftRouteFilter installed, enable it by adding it to your
`config/application.config.php` `modules` section:

    return array(
        'modules' => array(

            // ... your modules
            // ... Apigility modules

            'Shoplift\RouteFilter',  // Add this line
        ),
    );

Then, you can define automatic filtering and validation of query and route
parameters on specific routes by adding the following configuration blocks
to your `module.config.php` file of the relevant API module:

    return array(
        'router' => array( /* your routes */ ),
        'service_manager' => array( /* services */ ),
        // ... more config ...

        'shoplift-route-filters' => array(
            'my-api.rest.user' => array(
                'route' => array(
                    'user_id' => array(
                        'name' => 'user_id',
                        'required' => false,
                        'validators' => array(),
                        'filters' => array(
                            array(
                                'name' => 'Zend\\Filter\\ToInt',
                            ),
                        )
                    ),
                ),
                'query' => array(
                    'account_id' => array(
                        'name' => 'account_id'
                        'required' => false,
                        'validators' => array(
                            array(
                                'name' => 'Zend\\Validator\\Hex',
                            ),
                        ),
                        'filters' => array(
                            array(
                                'name' => 'Zend\\Filter\\StringToLower',
                            ),
                            array(
                                'name' => 'Zend\\Filter\\StringTrim',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

Under the `shoplift-route-filters` key, you define an associative array of
route names (as defined in your `routes` config section), where the value is
an associative array which may have the keys `route`, which defines input
filters for route parameters, and `query`, which defines input filters for
query parameters.

Under these two keys, input filters are defined for each named route or
query parameter. The structure of configuration here is identical to the
structure accepted by `Zend\InputFilter\Factory` - see
[the documentation for Zend\InputFilter](http://framework.zend.com/manual/current/en/modules/zend.input-filter.intro.html)
for details.

Note that filtering and validation happens after a route is matched. The
parameters you'll get in the Request and RouteMatch objects are the filtered
and validated ones.

If validation fails for a parameter you have validation defined for, the behaviour
is similar to Apigility's included content validation feature: a
'422 Unprocessable Entity' HTTP response is returned.


License & Acknowledgement
-------------------------
ShopliftRouteFilter was created by the (Shoppimon)[https://www.shoppimon.com/]
team and is released under the terms of the MIT license, as detailed in the
enclosed LICENSE file.

(c) 2015 Shoppimon LTD, all rights reserved.
