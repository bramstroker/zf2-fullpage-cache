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

        if (isset($routeConfig['params'])) {
            $params = $routeConfig['params'];
            if (!$this->matchParams($event->getRouteMatch(), $params)) {
                return false;
            }
        }

        if (isset($routeConfig['http_methods'])) {
            $methods = (array) $routeConfig['http_methods'];
            if (!in_array($event->getRequest()->getMethod(), $methods)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  RouteMatch $match
     * @param  array $routeConfig
     * @return bool
     * @todo This could be cleaned up some more
     */
    protected function matchParams(RouteMatch $match, $ruleParams)
    {
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
