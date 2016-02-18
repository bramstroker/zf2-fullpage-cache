<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch    as RouteMatchHttp;
use Zend\Mvc\Router\Console\RouteMatch as RouteMatchConsole;
use StrokerCache\Exception\BadConfigurationException;

class CacheAllExcept extends AbstractStrategy
{
    /**
     * @var array
     */
    protected $except;

    /**
     * {@inheritDoc}
     */
    public function shouldCache(MvcEvent $event)
    {
        $except = $this->getExcept();

        if (!isset($except['namespaces']) && !isset($except['controllers']) && !isset($except['actions'])) {
            throw new BadConfigurationException(
                  "At least one of ['namespaces', 'controllers', 'actions'] keys has to be set in the "
                . "\$config['strokercache']['strategies']['enabled']['" . __CLASS__ . "']['except'][] "
                . "confiuration array."
            );
        }

        $shouldCache = false;

        $routeMatch = $event->getRouteMatch();

        if ($routeMatch instanceof RouteMatchHttp || $routeMatch instanceof RouteMatchConsole) {

            $shouldCache = true;

            $controller  = $this->normalize($routeMatch->getParam('controller'));
            $action      = $this->normalize($routeMatch->getParam('action'));

            if (true === $shouldCache && isset($except['namespaces'])) {
                foreach ($except['namespaces'] as $exceptNamespace) {
                    if (0 === strpos($controller, $this->normalize($exceptNamespace))) {
                        $shouldCache = false;
                        break 1;
                    }
                }
            }

            if (true === $shouldCache && isset($except['controllers'])) {
                foreach ($except['controllers'] as $exceptController) {
                    if ($controller === $this->normalize($exceptController)) {
                        $shouldCache = false;
                        break 1;
                    }
                }
            }

            if (true === $shouldCache && isset($except['actions'])) {
                foreach ($except['actions'] as $exceptController => $exceptActions) {
                    if ($controller === $this->normalize($exceptController)) {
                        foreach ($exceptActions as $exceptAction) {
                            if ($action === $this->normalize($exceptAction)) {
                                $shouldCache = false;
                                break 2;
                            }
                        }
                    }
                }
            }

        }

        return $shouldCache;
    }

    /**
     * @return array
     */
    public function getExcept()
    {
        return $this->except;
    }

    /**
     * @param array $except
     */
    public function setExcept(array $except)
    {
        $this->except = $except;
    }

    /**
     * Normalize names before comparing
     *
     * @param type $string
     */
    protected function normalize($string)
    {
        $string = trim($string);

        $string = strtolower($string);

        return (string) $string;
    }
}
