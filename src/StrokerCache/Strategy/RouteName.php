<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\AbstractOptions;

class RouteName extends AbstractOptions implements StrategyInterface
{
    /**
     * @var array
     */
    protected $routes;

    /**
     * {@inheritDoc}
     */
    public function shouldCache(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if ($routeMatch === null) {
            return false;
        }

        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        if (!array_key_exists($routeName, $this->getRoutes()) && !in_array($routeName, $this->getRoutes())) {
            return false;
        }

        $routeConfig = $this->getRouteConfig($routeName);

        if (
            !$this->checkParams($routeMatch, $routeConfig) ||
            !$this->checkHttpMethod($event, $routeConfig)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if we should cache the request based on the params in the routematch
     *
     * @param  RouteMatch $match
     * @param  array $routeConfig
     * @return bool
     * @todo This could be cleaned up some more
     */
    protected function checkParams(RouteMatch $match, $routeConfig)
    {
        if (!isset($routeConfig['params'])) {
            return true;
        }

        $ruleParams = $routeConfig['params'];
        $params = $match->getParams();
        foreach ($ruleParams as $param => $value) {
            if (isset($params[$param])) {
                if (preg_match('/^\/.*\//', $value)) {
                    $regex = $value;
                    if (!preg_match($regex, $params[$param])) {
                        return false;
                    }
                } elseif ($value != $params[$param]) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if we should cache the request based on http method requested
     *
     * @param MvcEvent $event
     * @param $routeConfig
     * @return bool
     */
    protected function checkHttpMethod(MvcEvent $event, $routeConfig)
    {
        if (isset($routeConfig['http_methods'])) {
            $methods = (array) $routeConfig['http_methods'];
            if (!in_array($event->getRequest()->getMethod(), $methods)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $routeName
     * @return array
     */
    protected function getRouteConfig($routeName)
    {
        $routes = $this->getRoutes();
        if (!isset($routes[$routeName])) {
            return array();
        }
        return (array) $routes[$routeName];
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }
}
