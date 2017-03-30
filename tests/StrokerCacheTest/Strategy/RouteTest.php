<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\Route;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Route
     */
    private $strategy;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->strategy = new Route();
    }

    /**
     * @return array
     */
    public static function shouldCacheProvider()
    {
        return [
            'match' => [
                ['route/route1', 'route2'],
                'route/route1',
                true
            ],
            'nomatch' => [
                ['route/route1', 'route2'],
                'route3',
                false
            ],
            'match-params' => [
                [
                    'testroute' => [
                        'params' => ['param1' => 'value1']
                    ]
                ],
                'testroute',
                true,
                [
                    'param1' => 'value1',
                    'param2' => 'value2'
                ],
            ],
            'match-params-multivalue' => [
                [
                    'testroute' => [
                        'params' => ['param1' => ['value1', 'value2', 'value3']]
                    ]
                ],
                'testroute',
                true,
                [
                    'param1' => 'value3',
                ],
            ],
            'match-params-when-no-params-in-routematch' => [
                [
                    'testroute' => [
                        'params' => ['param1' => 'value1']
                    ]
                ],
                'testroute',
                true
            ],
            'nomatch-params' => [
                [
                    'testroute' => [
                        'params' => ['param1' => 'value2']
                    ]
                ],
                'testroute',
                false,
                [
                    'param1' => 'value1',
                    'param2' => 'value2'
                ],
            ],
            'match-regexparams' => [
                [
                    'testroute' => [
                        'params' => [
                            'param1' => '/val.*/']
                    ]
                ],
                'testroute',
                true,
                [
                    'param1' => 'value1',
                    'param2' => 'value2'
                ],
            ],
            'nomatch-regexparams' => [
                [
                    'testroute' => [
                        'params' => [
                            'param1' => '/val.*/']
                    ]
                ],
                'testroute',
                false,
                [
                    'param1' => 'foo',
                    'param2' => 'value2'
                ],
            ],
            'match-http-method' => [
                [
                    'foo' => [
                        'http_methods' => ['GET', 'POST']
                    ]
                ],
                'foo',
                true,
                [],
                'POST',
            ],
            'nomatch-http-method' => [
                [
                    'foo' => [
                        'http_methods' => ['GET']
                    ]
                ],
                'foo',
                false,
                [],
                'POST',
            ]
        ];
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

    public function testStrategyExtendsAbstractStrategy()
    {
        $this->assertInstanceOf('StrokerCache\Strategy\AbstractStrategy', $this->strategy);
    }
}
