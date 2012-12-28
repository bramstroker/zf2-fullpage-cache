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

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\ControllerName;
use StrokerCache\Strategy\RouteName;

class RouteNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerName
     */
    private $strategy;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->strategy = new RouteName();
    }

    /**
     * @return array
     */
    public static function shouldCacheProvider()
    {
        return array(
            'match' => array(
                array('route/route1', 'route2'),
                'route/route1',
                array(),
                true
            ),
            'nomatch' => array(
                array('route/route1', 'route2'),
                'route3',
                array(),
                false
            ),
            'match-params' => array(

                array(
                    array(
                        'name' => 'testroute',
                        'params' => array('param1' => 'value1')
                    )
                ),
                'testroute',
                array(
                    'param1' => 'value1',
                    'param2' => 'value2'
                ),
                true
            ),
            'nomatch-params' => array(
                array(
                    array(
                        'name' => 'testroute',
                        'params' => array('param1' => 'value2')
                    )
                ),
                'testroute',
                array(
                    'param1' => 'value1',
                    'param2' => 'value2'
                ),
                false
            ),

            'match-regexparams' => array(
                array(
                    array(
                        'name' => 'testroute',
                        'params' => array(
                            'param1' => '/val.*/')
                    )
                ),
                'testroute',
                array(
                    'param1' => 'value1',
                    'param2' => 'value2'
                ),
                true
            ),
        );
    }

    /**
     * @param array $routes
     * @param string $route
     * @param array $params
     * @param boolean $expectedResult
     * @dataProvider shouldCacheProvider
     */
    public function testShouldCache($routes, $route, $params, $expectedResult)
    {
        $this->strategy->setRoutes($routes);
        $routeMatch = new \Zend\Mvc\Router\RouteMatch($params);
        $routeMatch->setMatchedRouteName($route);
        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $mvcEvent->setRouteMatch($routeMatch);
        $this->assertEquals($expectedResult, $this->strategy->shouldCache($mvcEvent));
    }
}
