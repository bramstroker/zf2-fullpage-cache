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
     * @var MockInterface
     */
    protected $idGeneratorMock;

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    public function setUp()
    {
        $_SERVER['REQUEST_URI'] = '/someroute';
        $this->storageMock = \Mockery::mock('Zend\Cache\Storage\StorageInterface')
            ->shouldReceive('setItem')
            ->byDefault()
            ->shouldReceive('getItem')
            ->byDefault()
            ->getMock();

        $this->idGeneratorMock = \Mockery::mock('StrokerCache\IdGenerator\IdGeneratorInterface')
            ->shouldReceive('generate')
            ->byDefault()
            ->andReturn('/foo/bar')
            ->getMock();

        $this->cacheService = new CacheService($this->storageMock, new ModuleOptions(), $this->idGeneratorMock);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testLoadPageFromCache()
    {
        // Setup
        $mvcEvent = new MvcEvent();

        // Expectations
        $expectedContent = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

        $this->storageMock
            ->shouldReceive('hasItem')
            ->with('/foo/bar')
            ->andReturn(true)
            ->shouldReceive('getItem')
            ->with('/foo/bar')
            ->andReturn($expectedContent);

        $content = $this->cacheService->load($mvcEvent);
        $this->assertEquals($expectedContent, $content);
    }

    public function testLoadReturnsNullWhenPageIsNotInCache()
    {
        // Setup
        $mvcEvent = new MvcEvent();

        $this->storageMock
            ->shouldReceive('hasItem')
            ->andReturn(false);
        $this->assertNull($this->cacheService->load($mvcEvent));
    }

    public function testCancelLoadingUsingLoadEvent()
    {
        // Setup
        $mvcEvent = new MvcEvent();

        $this->storageMock
            ->shouldReceive('hasItem')
            ->with('/foo/bar')
            ->andReturn(true);

        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_LOAD, function () { return false; });

        $this->assertNull($this->cacheService->load($mvcEvent));
    }

    public function testSaveResponseIsNotCached()
    {
        // Setup
        $mvcEvent = new MvcEvent();

        // Expectations
        $this->storageMock->shouldReceive('setItem')->never();

        // Call second time with non caching event attached
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return false; });
        $this->cacheService->save($mvcEvent);
    }

    public function testSaveResponseIsCached()
    {
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return true; });

        $response = $this->getMvcEvent()->getResponse();

        $this->storageMock
            ->shouldReceive('setItem')
            ->once()
            ->with('/foo/bar', serialize($response));

        $this->cacheService->save($this->getMvcEvent());
    }

    public function testSaveContentIsCached()
    {
        $response = $this->getMvcEvent()->getResponse();
        $response->setContent('mockContent');

        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return true; });

        $this->storageMock
            ->shouldReceive('setItem')
            ->once()
            ->with('/foo/bar', $response->getContent());

        $this->cacheService->getOptions()->setCacheResponse(false);
        $this->cacheService->save($this->getMvcEvent());
    }

    public function testResponseIsCachedWhenOneListenerReturnsTrue()
    {
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return false; });
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return true; });
        $self = $this;
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () use ($self) {
            $self->fail('No more listeners should have been called anymore');
        });

        $this->storageMock->shouldReceive('setItem')->once();

        $this->cacheService->save($this->getMvcEvent());
    }

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
            ->with('/foo/bar', $expectedTags)
            ->getMock();
        $this->cacheService->setCacheStorage($storageMock);

        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return true; });

        $this->cacheService->save($this->getMvcEvent());
    }

    public function testClearByTags()
    {
        $tags = array('foo', 'bar');

        $storageMock = \Mockery::mock('Zend\Cache\Storage\TaggableInterface')
            ->shouldReceive('clearByTags')
            ->with(array('strokercache_foo', 'strokercache_bar'), null)
            ->getMock();
        $this->cacheService->setCacheStorage($storageMock);

        $this->cacheService->clearByTags($tags);
    }

    public function testClearByTagsIsSkippedForNonTaggableStorageAdapters()
    {
        $this->cacheService->clearByTags(array('foo'));
    }

    public function testGetSetOptions()
    {
        $options = new ModuleOptions();
        $this->cacheService->setOptions($options);
        $this->assertEquals($options, $this->cacheService->getOptions());
    }

    public function testSaveEventIsTriggered()
    {
        $self = $this;
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SAVE, function ($e) use ($self) {
            $self->assertInstanceOf('StrokerCache\Event\CacheEvent', $e);
        });

        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return true; });

        $this->cacheService->save($this->getMvcEvent());
    }

    public function testLoadEventIsTriggered()
    {
        // Setup
        $mvcEvent = new MvcEvent();

        $this->idGeneratorMock->shouldReceive('generate')->andReturn('foo-bar');

        $self = $this;
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_LOAD, function ($e) use ($self) {
            $self->assertInstanceOf('StrokerCache\Event\CacheEvent', $e);
            $self->assertEquals('foo-bar', $e->getCacheKey());
        });

        $this->storageMock
            ->shouldReceive('hasItem')
            ->andReturn(true);

        $this->cacheService->load($mvcEvent);
    }

    public function testSettersProvideFluentInterface()
    {
        $service = $this->cacheService
            ->setEventManager(new EventManager())
            ->setCacheStorage($this->storageMock)
            ->setOptions(new ModuleOptions());

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
}
