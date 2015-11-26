<?php

namespace StrokerCache\Strategy;

use Zend\Mvc\MvcEvent;

use StrokerCache\Strategy\AbstractStrategy as AbstractStrategyStrategyStrokerCache;
use StrokerCache\Exception\BadConfigurationException;

class ControllerCacheAll extends AbstractStrategyStrategyStrokerCache
{
    protected $except;

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

        $routeMatch  = $event->getRouteMatch();

        $controller  = $this->normalize($routeMatch->getParam('controller'));
        $action      = $this->normalize($routeMatch->getParam('action'));

        $shouldCache = true;

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

        return $shouldCache;
    }

    public function getExcept()
    {
        return $this->except;
    }

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
