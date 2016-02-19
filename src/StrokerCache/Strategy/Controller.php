<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use Zend\Mvc\MvcEvent;

class Controller extends AbstractStrategy
{
    /**
     * @var array
     */
    protected $controllers;

    /**
     * {@inheritDoc}
     */
    public function shouldCache(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();

        if (null === $routeMatch) {
            return false;
        }

        $controller = $routeMatch->getParam('controller');

        return in_array($controller, $this->getControllers());
    }

    /**
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * @param array $controllers
     */
    public function setControllers(array $controllers)
    {
        $this->controllers = $controllers;
    }
}
