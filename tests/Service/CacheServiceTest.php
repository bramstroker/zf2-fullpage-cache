<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Service;

use Mockery;
use Mockery\MockInterface;
use StrokerCache\Exception\UnsupportedAdapterException;
use StrokerCache\IdGenerator\IdGeneratorInterface;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\Storage\TaggableInterface;
use Laminas\EventManager\EventManager;
use StrokerCache\Event\CacheEvent;
use StrokerCache\Service\CacheService;
use StrokerCache\Options\ModuleOptions;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;

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
        $this->storageMock = Mockery::mock(StorageInterface::class)
            ->shouldReceive('setItem')
            ->byDefault()
            ->shouldReceive('getItem')
            ->byDefault()
            ->getMock();

        $this->idGeneratorMock = Mockery::mock(IdGeneratorInterface::class)
            ->shouldReceive('generate')
            ->byDefault()
            ->andReturn('/foo/bar')
            ->getMock();

        $this->cacheService = new CacheService($this->storageMock, new ModuleOptions(), $this->idGeneratorMock);
    }

    public function tearDown()
    {
        Mockery::close();
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

    public function testSaveEventHasCacheKey()
    {
        $response = $this->getMvcEvent()->getResponse();
        $response->setContent('mockContent');

        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return true; });
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SAVE, function (CacheEvent $e) {
            $this->assertNotNull($e->getCacheKey());
        });

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
        $storageMock = Mockery::mock('Laminas\Cache\Storage\TaggableInterface')
            ->shouldReceive('setItem')
            ->shouldReceive('setTags')
            ->once()
            ->with('/foo/bar', $expectedTags)
            ->getMock();
        $this->cacheService->setCacheStorage($storageMock);

        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return true; });

        $this->cacheService->save($this->getMvcEvent());
    }

    public function testSaveGeneratesCustomTags()
    {
        $expectedTags = array(
            'strokercache_route_home',
            'strokercache_controller_myTestController',
            'strokercache_param_someParam_someValue',
            'strokercache_custom_tag',
        );

        $this->getMvcEvent()->getRouteMatch()->setMatchedRouteName('home');
        $this->getMvcEvent()->getRouteMatch()->setParam('controller', 'myTestController');
        $this->getMvcEvent()->getRouteMatch()->setParam('someParam', 'someValue');

        // Storage mock should implement the TaggableInterface
        $storageMock = Mockery::mock('Laminas\Cache\Storage\TaggableInterface')
            ->shouldReceive('setItem')
            ->once()
            ->shouldReceive('setTags')
            ->once()
            ->with('/foo/bar', $expectedTags)
            ->getMock();
        $this->cacheService->setCacheStorage($storageMock);

        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function () { return true; });
        $this->cacheService->getEventManager()->attach(CacheEvent::EVENT_SAVE, function ($event) {
            $event->setTags(['custom_tag']);
        });

        $this->cacheService->save($this->getMvcEvent());
    }

    public function testClearByTags()
    {
        $tags = ['foo', 'bar'];

        $storageMock = Mockery::mock(TaggableInterface::class)
            ->shouldReceive('clearByTags')
            ->with(['strokercache_foo', 'strokercache_bar'], null)
            ->getMock();
        $this->cacheService->setCacheStorage($storageMock);

        $this->cacheService->clearByTags($tags);
    }

    public function testClearByTagsThrowsExceptionForNonTaggableStorageAdapters()
    {
        $this->expectException(UnsupportedAdapterException::class);
        $this->cacheService->clearByTags(['foo']);
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
            $self->assertInstanceOf(CacheEvent::class, $e);
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
            $self->assertInstanceOf(CacheEvent::class, $e);
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
            $this->mvcEvent->setRouteMatch(new RouteMatch([]));
            $this->mvcEvent->setResponse(new \Laminas\Http\Response());
        }

        return $this->mvcEvent;
    }
}
