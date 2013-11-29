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
     * {@inheritDoc}
     */
    public function shouldCache(MvcEvent $event)
    {
        if ($event->getRouteMatch() === null) {
            return false;
        }

        foreach ($this->getRoutes() as $routeOptions) {
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
     * @param  array $params
     * @param  array $ruleParams
     * @return bool
     */
    protected function matchParams(array $params, $ruleParams)
    {
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
