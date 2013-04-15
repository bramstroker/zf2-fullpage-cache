<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Service;

use StrokerCache\Service\CacheService;
use StrokerCache\Options\ModuleOptions;
use Zend\Mvc\MvcEvent;

class CacheServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheService
     */
    private $cacheService;

    /**
     * @var \Mockery\MockInterface
     */
    private $storageMock;

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * Setup cache service and mocks
     */
    public function setUp()
    {
        $_SERVER['REQUEST_URI'] = '/someroute';
        $this->storageMock = \Mockery::mock('Zend\Cache\Storage\StorageInterface');
        $this->cacheService = new CacheService($this->storageMock, new ModuleOptions());
    }

    /**
     * @return MvcEvent
     */
    protected function getMvcEvent()
    {
        if ($this->mvcEvent === null) {
            $this->mvcEvent = new MvcEvent();
            $this->mvcEvent->setRouteMatch(new \Zend\Mvc\Router\Http\RouteMatch(array()));
            $this->mvcEvent->setResponse(new \Zend\Http\Response());
        }

        return $this->mvcEvent;
    }

    /**
     * testLoadPageFromCache
     */
    public function testLoadPageFromCache()
    {
        $expectedContent = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

        $this->storageMock
            ->shouldReceive('hasItem')
            ->andReturn(true)
            ->shouldReceive('getItem')
            ->andReturn($expectedContent);

        $content = $this->cacheService->load();
        $this->assertEquals($expectedContent, $content);
    }

    /**
     * testLoadReturnsNullWhenPageIsNotInCache
     */
    public function testLoadReturnsNullWhenPageIsNotInCache()
    {
        $this->storageMock
            ->shouldReceive('hasItem')
            ->andReturn(false);
        $this->assertNull($this->cacheService->load());
    }

    /**
     * testSaveResponseIsNotCached
     */
    public function testSaveResponseIsNotCached()
    {
        // Setup
        $mvcEvent = new MvcEvent();
        $strategyMock = \Mockery::mock('StrokerCache\Strategy\StrategyInterface')
            ->shouldReceive('shouldCache')
            ->andReturn(false)
            ->getMock();

        // Expectations
        $this->storageMock->shouldReceive('setItem')->never();

        // Call once without any strategies confirured
        $this->cacheService->save($mvcEvent);

        // Call second time with non caching strategy added
        $this->cacheService->addStrategy($strategyMock);
        $this->cacheService->save($mvcEvent);
    }

    /**
     * testSaveResponseIsCached
     */
    public function testSaveResponseIsCached()
    {
        $expectedContent = 'Some dummy content';

        $this->getMvcEvent()->getResponse()->setContent($expectedContent);

        $strategyMock = \Mockery::mock('StrokerCache\Strategy\StrategyInterface')
            ->shouldReceive('shouldCache')
            ->with($this->getMvcEvent())
            ->once()
            ->andReturn(true)
            ->getMock();
        $this->cacheService->addStrategy($strategyMock);

        $this->storageMock
            ->shouldReceive('setItem')
            ->once()
            ->with(\Mockery::any(), $expectedContent);

        $this->cacheService->save($this->getMvcEvent());
    }

    /**
     * testSaveGeneratesCorrectTags
     */
    public function testSaveGeneratesCorrectTags()
    {
        $expectedTags = array(
            'strokercache_route_home',
            'strokercache_controller_myTestController',
            'strokercache_param_someParam_someValue'
        );

        $this->getMvcEvent()->getRouteMatch()->setMatchedRouteName('home');
        $this->getMvcEvent()->getRouteMatch()->setParam('controller', 'myTestController');
        $this->getMvcEvent()->getRouteMatch()->setParam('someParam', 'someValue');

        // Storage mock should implement the TaggableInterface
        $storageMock = \Mockery::mock('Zend\Cache\Storage\TaggableInterface')
            ->shouldReceive('setItem')
            ->shouldReceive('setTags')
            ->once()
            ->with(\Mockery::any(), $expectedTags)
            ->getMock();
        $this->cacheService->setCacheStorage($storageMock);

        $strategyMock = \Mockery::mock('StrokerCache\Strategy\StrategyInterface')
            ->shouldReceive('shouldCache')
            ->andReturn(true)
            ->getMock();
        $this->cacheService->addStrategy($strategyMock);

        $this->cacheService->save($this->getMvcEvent());
    }
}
