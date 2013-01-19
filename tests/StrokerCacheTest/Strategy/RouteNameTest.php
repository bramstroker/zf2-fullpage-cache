<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\RouteName;

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
     * @param array   $routes
     * @param string  $route
     * @param array   $params
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

    /**
     * testShouldCacheReturnsFalseOnNoRouteMatchObject
     */
    public function testShouldCacheReturnsFalseWhenNoRouteMatchIsSet()
    {
        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $this->assertFalse($this->strategy->shouldCache($mvcEvent));
    }
}
