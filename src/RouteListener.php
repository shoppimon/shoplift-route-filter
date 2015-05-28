<?php

/**
 * Shoplift\RouteFilter - Easy per-route input filtering for ZF2 / Apigility apps
 *
 * @copyright (c) 2015 Shoppimon LTD
 */

namespace Shoplift\RouteFilter;

use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\InputFilter\Factory as FilterFactory;
use Zend\Stdlib\Parameters;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class RouteListener implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var array|null
     */
    protected $config = null;

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    public function __invoke(MvcEvent $event)
    {
        $route = $event->getRouteMatch();
        $paramTypeFilters = $this->getFiltersForRoute($route->getMatchedRouteName());

        foreach ($paramTypeFilters as $type => $filters) {
            $type = strtolower($type);
            switch ($type) {
                case 'query':
                    $request = $event->getRequest();
                    if ($request instanceof Request) {
                        $filtered = $this->filterParameters($filters, $request->getQuery());
                        if ($filtered instanceof ApiProblemResponse) {
                            return $filtered;
                        }
                        $request->setQuery($filtered);
                    }
                    break;

                case 'route':
                    $filtered = $this->filterParameters($filters, $route->getParams());
                    if ($filtered instanceof ApiProblemResponse) {
                        return $filtered;
                    }
                    foreach ($filtered as $key => $value) {
                        $route->setParam($key, $value);
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * @param FilterFactory $filterFactory
     * @return $this
     */
    public function setFilterFactory(FilterFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
        return $this;
    }

    /**
     * @return FilterFactory
     */
    public function getFilterFactory()
    {
        if (! $this->filterFactory) {
            $this->filterFactory = new FilterFactory();
            $this->filterFactory->setInputFilterManager($this->getServiceLocator()->get('InputFilterManager'));
        }
        return $this->filterFactory;
    }

    /**
     *
     * @param array            $filterSpec
     * @param array|Parameters $parameters
     * @return array|Parameters|ApiProblemResponse
     */
    protected function filterParameters(array $filterSpec, $parameters)
    {
        $filter = $this->getFilterFactory()->createInputFilter($filterSpec);
        $filter->setData($parameters);
        if ($filter->isValid()) {
            $data = $filter->getValues();
            foreach ($parameters as $param => $value) {
                if (isset($data[$param])) {
                    $parameters[$param] = $data[$param];
                }
            }
        } else {
            // Data is not valid
            return new ApiProblemResponse(
                new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => $filter->getMessages(),
                ))
            );
        }

        return $parameters;
    }

    /**
     * Get filter configuration for a matched route
     *
     * @param string $route
     * @return array
     */
    protected function getFiltersForRoute($route)
    {
        if ($this->config === null) {
            $config = $this->getServiceLocator()->get('config');
            if (is_array($config) && isset($config['shoplift-route-filters'])) {
                $this->config = $config['shoplift-route-filters'];
            } else {
                $this->config = array();
            }
        }

        if (isset($this->config[$route])) {
            return $this->config[$route];
        }

        return array();
    }
}
