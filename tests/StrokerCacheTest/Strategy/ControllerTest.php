<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\Controller;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerName
     */
    private $strategy;

    public function setUp()
    {
        $this->strategy = new Controller();
    }

    /**
     * @return array
     */
    public static function shouldCacheProvider()
    {
        return array(
            array(array('Namespace\TestController'), 'Namespace\TestController', true),
            array(array(), 'Namespace\TestController', false),
            array(array('Namespace\TestController2'), 'Namespace\TestController', false),
        );
    }

    /**
     * @param array   $controllers
     * @param string  $requestedController
     * @param boolean $expectedResult
     * @dataProvider shouldCacheProvider
     */
    public function testShouldCache($controllers, $requestedController, $expectedResult)
    {
        $this->strategy->setControllers($controllers);
        $mvcEvent = new MvcEvent();
        $mvcEvent->setControllerClass($requestedController);
        $mvcEvent->setRouteMatch(new RouteMatch(array('controller' => $requestedController)));
        $this->assertEquals($expectedResult, $this->strategy->shouldCache($mvcEvent));
    }

    public function testStrategyExtendsAbstractStrategy()
    {
        $this->assertInstanceOf('StrokerCache\Strategy\AbstractStrategy', $this->strategy);
    }

    /**
     * In the case that a route is not matched - i.e. RouteMatch is null - strategy should always return false.
     * Caching a non-matched route does not make any sense.
     */
    public function testShouldCacheWhenRouteMatchIsNull()
    {
        $this->strategy->setControllers(array());
        $mvcEvent = new MvcEvent();
        $this->assertFalse($this->strategy->shouldCache($mvcEvent));
    }
}
