<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\ControllerName;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class ControllerNameTest extends \PHPUnit_Framework_TestCase
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
        $this->strategy = new ControllerName();
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
}
