<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Service;

use Mockery\MockInterface;
use Zend\EventManager\EventManager;
use StrokerCache\Event\CacheEvent;
use StrokerCache\Service\CacheService;
use StrokerCache\Options\ModuleOptions;
use StrokerCache\Strategy\RouteName;
use Zend\Mvc\MvcEvent;

class CacheServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @var MockInterface
     */
    protected $storageMock;

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
        $this->storageMock = \Mockery::mock('Zend\Cache\Storage\StorageInterface')
            ->shouldReceive('setItem')
            ->byDefault()
            ->shouldReceive('getItem')
            ->byDefault()
            ->getMock();

        $this->cacheService = new CacheService($this->storageMock, new ModuleOptions());
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
        $response = $this->getMvcEvent()->getResponse();

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
            ->with(\Mockery::any(), serialize($response));

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

    public function testClearByTags()
    {
        $tags = array('foo', 'bar');

        $storageMock = \Mockery::mock('Zend\Cache\Storage\TaggableInterface')
            ->shouldReceive('clearByTags')
            ->with(array('strokercache_foo', 'strokercache_bar'))
            ->getMock();
        $this->cacheService->setCacheStorage($storageMock);

        $this->cacheService->clearByTags($tags);
    }

    public function testClearByTagsIsSkippedForNonTaggableStorageAdapters()
    {
        $this->cacheService->clearByTags(array('foo'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateIdThrowsExceptionWhenRequestUriIsNotAvailable()
    {
        unset($_SERVER['REQUEST_URI']);
        $this->cacheService->load(new MvcEvent());
    }

    public function testGetSetOptions()
    {
        $options = new ModuleOptions();
        $this->cacheService->setOptions($options);
        $this->assertEquals($options, $this->cacheService->getOptions());
    }

    public function testGetSetStrategies()
    {
        $strategies = array(
            new RouteName()
        );
        $this->cacheService->setStrategies($strategies);
        $this->assertEquals($strategies, $this->cacheService->getStrategies());
    }

    public function testSaveEventIsTriggered()
    {
        $self = $this;
        $this->setEventManager($this->cacheService, CacheEvent::EVENT_SAVE, function($e) use ($self) {
            $self->assertInstanceOf('StrokerCache\Event\CacheEvent', $e);
        });

        $strategyMock = \Mockery::mock('StrokerCache\Strategy\StrategyInterface')
            ->shouldReceive('shouldCache')
            ->andReturn(true)
            ->getMock();
        $this->cacheService->addStrategy($strategyMock);

        $this->cacheService->save($this->getMvcEvent());
    }

    public function testLoadEventIsTriggered()
    {
        $cacheKey = md5($_SERVER['REQUEST_URI']);

        $self = $this;
        $this->setEventManager($this->cacheService, CacheEvent::EVENT_LOAD, function($e) use ($self, $cacheKey) {
            $self->assertInstanceOf('StrokerCache\Event\CacheEvent', $e);
            $self->assertEquals($cacheKey, $e->getCacheKey());
        });

        $this->storageMock
            ->shouldReceive('hasItem')
            ->andReturn(true);

        $this->cacheService->load();
    }

    public function testSettersProvideFluentInterface()
    {
        $service = $this->cacheService
            ->setEventManager(new EventManager())
            ->setCacheStorage($this->storageMock)
            ->setOptions(new ModuleOptions())
            ->setStrategies(array());

        $this->assertEquals($this->cacheService, $service);
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

    protected function setEventManager(CacheService $cacheService, $event = null, $callback = null)
    {
        $events = new EventManager;

        if (null !== $event && null !== $callback) {
            $events->attach($event, $callback);
        }

        $cacheService->setEventManager($events);
        return $cacheService;
    }
}
