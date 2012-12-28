<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use Zend\Mvc\MvcEvent;
use Zend\Stdlib\AbstractOptions;

class RouteName extends AbstractOptions implements StrategyInterface
{
    /**
     * @var array
     */
    private $routes;

    /**
     * True if the request should be cached
     *
     * @param MvcEvent $event
     * @return boolean
     */
    public function shouldCache(MvcEvent $event)
    {
        foreach($this->getRoutes() as $routeOptions) {
            if (is_string($routeOptions)) {
                $route = $routeOptions;
                $params = array();
            } else {
                $route = $routeOptions['name'];
                $params = isset($routeOptions['params']) ? $routeOptions['params'] : array();
            }

            if (
                $route == $event->getRouteMatch()->getMatchedRouteName() &&
                $this->matchParams($event->getRouteMatch()->getParams(), $params)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $params
     * @param $ruleParams
     * @return bool
     */
    protected function matchParams(array $params, $ruleParams)
    {
        foreach($ruleParams as $param => $value) {
            if (isset($params[$param])) {
                // Regex matching
                if (preg_match('/^\/.*\//', $value)) {
                    $regex = $value;
                    if (!preg_match($regex, $params[$param])) {
                        return false;
                    }
                // Literal matching
                } elseif ($value != $params[$param]) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Cache tags to use for this page
     *
     * @param \Zend\Mvc\MvcEvent $event
     * @return array
     */
    public function getTags(MvcEvent $event)
    {
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $tags = array(
            'strokercache_route_' . $routeName
        );
        foreach($event->getRouteMatch()->getParams() as $key => $value) {
            $tags[] = 'strokercache_route_' . $routeName . '_p:' . $key . '_' . $value;
        }
        return $tags;
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
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }
}
