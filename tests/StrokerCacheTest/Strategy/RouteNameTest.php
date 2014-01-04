<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\RouteName;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class RouteNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouteName
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
                true
            ),
            'nomatch' => array(
                array('route/route1', 'route2'),
                'route3',
                false
            ),
            'match-params' => array(
                array(
                    'testroute' => array(
                        'params' => array('param1' => 'value1')
                    )
                ),
                'testroute',
                true,
                array(
                    'param1' => 'value1',
                    'param2' => 'value2'
                ),
            ),
            'match-params-multivalue' => array(
                array(
                    'testroute' => array(
                        'params' => array('param1' => array('value1', 'value2', 'value3'))
                    )
                ),
                'testroute',
                true,
                array(
                    'param1' => 'value3',
                ),
            ),
            'match-params-when-no-params-in-routematch' => array(
                array(
                    'testroute' => array(
                        'params' => array('param1' => 'value1')
                    )
                ),
                'testroute',
                true
            ),
            'nomatch-params' => array(
                array(
                    'testroute' => array(
                        'params' => array('param1' => 'value2')
                    )
                ),
                'testroute',
                false,
                array(
                    'param1' => 'value1',
                    'param2' => 'value2'
                ),
            ),
            'match-regexparams' => array(
                array(
                    'testroute' => array(
                        'params' => array(
                            'param1' => '/val.*/')
                    )
                ),
                'testroute',
                true,
                array(
                    'param1' => 'value1',
                    'param2' => 'value2'
                ),
            ),
            'nomatch-regexparams' => array(
                array(
                    'testroute' => array(
                        'params' => array(
                            'param1' => '/val.*/')
                    )
                ),
                'testroute',
                false,
                array(
                    'param1' => 'foo',
                    'param2' => 'value2'
                ),
            ),
            'match-http-method' => array(
                array(
                    'foo' => array(
                        'http_methods' => array('GET', 'POST')
                    )
                ),
                'foo',
                true,
                array(),
                'POST',
            ),
            'nomatch-http-method' => array(
                array(
                    'foo' => array(
                        'http_methods' => array('GET')
                    )
                ),
                'foo',
                false,
                array(),
                'POST',
            )
        );
    }

    /**
     * @param array   $routes
     * @param string  $route
     * @param boolean $expectedResult
     * @param array   $params
     * @param string  $httpMethod
     * @dataProvider shouldCacheProvider
     */
    public function testShouldCache($routes, $route, $expectedResult, $params = array(), $httpMethod = null)
    {
        $this->strategy->setRoutes($routes);
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName($route);

        $request = new Request();

        if ($httpMethod !== null) {
            $request->setMethod($httpMethod);
        }

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRouteMatch($routeMatch);
        $mvcEvent->setRequest($request);
        $this->assertEquals($expectedResult, $this->strategy->shouldCache($mvcEvent));
    }

    public function testShouldCacheReturnsFalseWhenNoRouteMatchIsSet()
    {
        $mvcEvent = new MvcEvent();
        $this->assertFalse($this->strategy->shouldCache($mvcEvent));
    }
}
