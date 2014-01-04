<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Listener;

use Mockery as M;
use StrokerCache\Event\CacheEvent;
use StrokerCache\Listener\ShouldCacheStrategyListener;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Http\PhpEnvironment\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use StrokerCache\Listener\CacheListener;
use StrokerCache\Options\ModuleOptions;

class ShouldCacheStrategyListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $eventManagerMock;

    /**
     * @var \Mockery\MockInterface
     */
    protected $strategyMock;

    /**
     * @var ShouldCacheStrategyListener
     */
    protected $listener;

    public function setUp()
    {
        $this->eventManagerMock = M::mock('Zend\EventManager\EventManagerInterface');
        $this->strategyMock = M::mock('StrokerCache\Strategy\StrategyInterface');
        $this->listener = new ShouldCacheStrategyListener($this->strategyMock);
    }

    public function testCorrectListenersIsAttached()
    {
        $this->eventManagerMock
            ->shouldReceive('attach')
            ->once()
            ->with(CacheEvent::EVENT_SHOULDCACHE, array($this->listener, 'shouldCache'), M::any());

        $this->listener->attach($this->eventManagerMock);
    }

    public function testShouldCacheCallsShouldCacheOnStrategy()
    {
        $mvcEvent = new MvcEvent();
        $cacheEvent = new CacheEvent();
        $cacheEvent->setMvcEvent($mvcEvent);
        $this->strategyMock
            ->shouldReceive('shouldCache')
            ->once()
            ->with($mvcEvent);

        $this->listener->shouldCache($cacheEvent);
    }
}
