<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Listener;

use Mockery as M;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use StrokerCache\Listener\CacheListener;
use StrokerCache\Options\ModuleOptions;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\ResponseInterface;

class CacheListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $eventManagerMock;

    /**
     * @var \Mockery\MockInterface
     */
    protected $cacheServiceMock;

    /**
     * @var CacheListener
     */
    protected $cacheListener;

    public function setUp()
    {
        $this->eventManagerMock = M::mock('Laminas\EventManager\EventManagerInterface');
        $this->cacheServiceMock = M::mock('StrokerCache\Service\CacheService');
        $this->cacheListener = new CacheListener($this->cacheServiceMock, new ModuleOptions());
    }

    public function testCorrectListenersAreAttached()
    {
        $this->eventManagerMock
            ->shouldReceive('attach')
            ->once()
            ->with('route', array($this->cacheListener, 'onRoute'), M::any());

        $this->eventManagerMock
            ->shouldReceive('attach')
            ->once()
            ->with('finish', array($this->cacheListener, 'onFinish'), M::any());

        $this->cacheListener->attach($this->eventManagerMock);
    }

    public function testPageIsLoadedFromCacheAndSetOnResponse()
    {
        $expectedResponse = new HttpResponse();
        $expectedResponse->setContent('foo');

        $this->cacheServiceMock
            ->shouldReceive('load')
            ->once()
            ->andReturn(serialize($expectedResponse));

        $this->cacheServiceMock
            ->shouldReceive('save')
            ->never();

        $mvcEvent = $this->createMvcEvent();

        $response = $this->cacheListener->onRoute($mvcEvent);

        $this->assertEquals($expectedResponse->getContent(), $response->getContent());

        $this->cacheListener->onFinish($mvcEvent);
    }

    public function testContentIsLoadedFromCacheAndSetOnResponse()
    {
        $expectedResponse = new HttpResponse();
        $expectedResponse->setContent('mockContent');

        $this->cacheServiceMock
            ->shouldReceive('load')
            ->once()
            ->andReturn('mockContent');

        $this->cacheServiceMock
            ->shouldReceive('save')
            ->never();

        $mvcEvent = $this->createMvcEvent();

        $this->cacheListener->getOptions()->setCacheResponse(false);
        $response = $this->cacheListener->onRoute($mvcEvent);

        $this->assertEquals($expectedResponse->getContent(), $response->getContent());

        $this->cacheListener->onFinish($mvcEvent);
    }

    public function testResponseHeaderIsSentOnCacheHit()
    {
        $mvcEvent = $this->createMvcEvent();

        $this->cacheServiceMock->shouldReceive('load')->andReturn(serialize($mvcEvent->getResponse()));

        $response = $this->cacheListener->onRoute($mvcEvent);

        /** @var \Laminas\Http\Headers $headers */
        $headers = $response->getHeaders();

        $this->assertTrue($headers->has('X-Stroker-Cache'));
        $this->assertStringStartsWith('X-Stroker-Cache: Hit', $headers->toString());
    }

    public function testResponseHeaderIsSentOnCacheMiss()
    {
        $this->cacheServiceMock->shouldReceive('load')->andReturn(null);

        $mvcEvent = $this->createMvcEvent();;

        $this->cacheListener->onRoute($mvcEvent);

        /** @var \Laminas\Http\Headers $headers */
        $headers = $mvcEvent->getResponse()->getHeaders();

        $this->assertTrue($headers->has('X-Stroker-Cache'));
        $this->assertStringStartsWith('X-Stroker-Cache: Miss', $headers->toString());
    }

    public function testPageNotFoundInCacheAndSavedOnFinish()
    {
        $this->cacheServiceMock
            ->shouldReceive('load')
            ->once()
            ->andReturn(null);

        $mvcEvent = $this->createMvcEvent();

        $this->cacheServiceMock
            ->shouldReceive('save')
            ->once()
            ->with($mvcEvent);

        $this->cacheListener->onRoute($mvcEvent);

        $this->cacheListener->onFinish($mvcEvent);
    }

    public function testOnRouteIsSkippedWhenNoHttpRequest()
    {
        $mvcEvent = $this->createMvcEvent(M::mock('Zend\StdLib\RequestInterface'));
        $response = $this->cacheListener->onRoute($mvcEvent);
        $this->assertNull($response);
    }

    public function testOnFinishIsSkippedWhenNoHttpRequest()
    {
        $mvcEvent = $this->createMvcEvent(M::mock('Zend\StdLib\RequestInterface'));
        $response = $this->cacheListener->onFinish($mvcEvent);
        $this->assertNull($response);
    }

    /**
     * @param  RequestInterface  $request
     * @param  ResponseInterface $response
     * @return MvcEvent
     */
    protected function createMvcEvent(RequestInterface $request = null, ResponseInterface $response = null)
    {
        if ($request === null) {
            $request = new HttpRequest();
        }

        if ($response === null) {
            $response = new HttpResponse();
        }

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest($request);
        $mvcEvent->setResponse($response);

        return $mvcEvent;
    }
}
