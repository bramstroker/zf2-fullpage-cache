<?php

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\CacheAllExcept;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class CacheAllExceptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerName
     */
    private $strategy;

    public function setUp()
    {
        $this->strategy = new CacheAllExcept();
    }

    /**
     * @return array
     */
    public static function shouldCacheProvider()
    {
        $except = array(

            'namespaces' => array(
                'Namespace\Controller\Console',
                'Namespace\Controller\Debug',
            ),

            'controllers' => array(
                'Namespace\Controller\Company\Contact\Index',
                'Namespace\Controller\Company\Mail\Index',
            ),

            'actions' => array(
                'Namespace\Controller\Media\Newsletters\Subscribe' => array(
                    'index',
                    'another',
                    'more',
                )
            ),

        );

        return array(

            // test for namespaces

            array( $except, 'Namespace\Controller\Console\Index'                     , null      , false ),
            array( $except, 'Namespace\Controller\Console\Index'                     , 'index'   , false ),
            array( $except, 'Namespace\Controller\Console\Another'                   , null      , false ),
            array( $except, 'Namespace\Controller\Console\Another'                   , 'boo'     , false ),
            array( $except, 'Namespace\Controller\Console\Another\Another\Another'   , null      , false ),
            array( $except, 'Namespace\Controller\Console\Another\Another\Another'   , 'bar'     , false ),

            array( $except, 'Namespace\Controller\Foo\Bar'                           , null      , true  ),
            array( $except, 'Namespace\Controller\Foo\Bar'                           , 'boo'     , true  ),


            // test for controllers

            array( $except, 'Namespace\Controller\Company\Contact\Index'             , null      , false ),
            array( $except, 'Namespace\Controller\Company\Contact\Index'             , 'index'   , false ),
            array( $except, 'Namespace\Controller\Company\Mail\Index'                , null      , false ),
            array( $except, 'Namespace\Controller\Company\Mail\Index'                , 'boo'     , false ),

            array( $except, 'Namespace\Controller\Company\Bar\Index'                 , null      , true  ),
            array( $except, 'Namespace\Controller\Company\Bar\Index'                 , 'bar'     , true  ),


            // test for actions

            array( $except, 'Namespace\Controller\Media\Newsletters\Subscribe'       , 'index'   , false ),
            array( $except, 'Namespace\Controller\Media\Newsletters\Subscribe'       , 'another' , false ),
            array( $except, 'Namespace\Controller\Media\Newsletters\Subscribe'       , 'more'    , false ),

            array( $except, 'Namespace\Controller\Media\Newsletters\Subscribe'       , 'bar'     , true  ),


            // and finally

            array( $except, 'Another\Different\Controller'                           , 'bar'     , true  ),

        );
    }

    /**
     * @param array   $except
     * @param string  $requestedController
     * @param boolean $expectedResult
     * @dataProvider shouldCacheProvider
     */
    public function testShouldCache($except, $requestedController, $requestedAction, $expectedResult)
    {
        $this->strategy->setExcept($except);

        $mvcEvent = new MvcEvent();
        $mvcEvent->setControllerClass($requestedController);
        $mvcEvent->setRouteMatch(new RouteMatch(array('controller' => $requestedController, 'action' => $requestedAction)));

        $this->assertEquals($expectedResult, $this->strategy->shouldCache($mvcEvent));
    }

    /**
     * @expectedException StrokerCache\Exception\BadConfigurationException
     */
    public function testShouldCacheException()
    {
        $except = array('missing' => 1);

        $this->strategy->setExcept($except);

        $mvcEvent = new MvcEvent();
        $mvcEvent->setControllerClass('Namespace\Controller\Console');
        $mvcEvent->setRouteMatch(new RouteMatch(array('controller' => 'Boo', 'action' => 'foo')));

        $this->strategy->shouldCache($mvcEvent);
    }

}
