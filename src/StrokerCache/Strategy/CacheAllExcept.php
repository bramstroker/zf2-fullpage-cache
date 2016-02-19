<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use Zend\Mvc\MvcEvent;

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

        $routeMatch = $event->getRouteMatch();

        if (null === $routeMatch) {
            return false;
        }

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
     * @param string $string
     * @return string
     */
    protected function normalize($string)
    {
        $string = trim($string);

        $string = strtolower($string);

        return $string;
    }
}