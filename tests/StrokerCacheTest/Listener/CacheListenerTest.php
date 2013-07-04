<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

use Mockery as M;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Http\PhpEnvironment\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use StrokerCache\Listener\CacheListener;

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
        $this->eventManagerMock = M::mock('Zend\EventManager\EventManagerInterface');
        $this->cacheServiceMock = M::mock('StrokerCache\Service\CacheService');
        $this->cacheListener = new CacheListener($this->cacheServiceMock);
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

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest(new HttpRequest());
        $mvcEvent->setResponse(new HttpResponse());

        $response = $this->cacheListener->onRoute($mvcEvent);

        $this->assertEquals($expectedResponse, $response);

        $this->cacheListener->onFinish($mvcEvent);
    }

    public function testPageNotFoundInCacheAndSavedOnFinish()
    {
        $this->cacheServiceMock
            ->shouldReceive('load')
            ->once()
            ->andReturn(null);

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest(new HttpRequest());

        $this->cacheServiceMock
            ->shouldReceive('save')
            ->once()
            ->with($mvcEvent);

        $this->cacheListener->onRoute($mvcEvent);

        $this->cacheListener->onFinish($mvcEvent);
    }

    public function testOnRouteIsSkippedWhenNoHttpRequest()
    {
        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest(M::mock('Zend\StdLib\RequestInterface'));
        $response = $this->cacheListener->onRoute($mvcEvent);
        $this->assertNull($response);
    }

    public function testOnFinishIsSkippedWhenNoHttpRequest()
    {
        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest(M::mock('Zend\StdLib\RequestInterface'));
        $response = $this->cacheListener->onFinish($mvcEvent);
        $this->assertNull($response);
    }
}
