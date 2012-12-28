<?php
/*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
* This software consists of voluntary contributions made by many individuals
* and is licensed under the MIT license.
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
            $route = (isset($routeOptions['name'])) ? $routeOptions['name'] : $routeOptions;
            $params = (isset($routeOptions['params'])) ? $routeOptions['params'] : array();

            if (
                $route == $event->getRouteMatch()->getMatchedRouteName() &&
                $this->matchParams($event->getRouteMatch()->getParams(), $params)
            ) {
                return true;
            }
        }
        return false;


        return in_array($routeName, $this->getRoutes());
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
