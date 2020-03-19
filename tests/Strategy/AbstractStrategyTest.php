<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Strategy;

use Mockery;
use StrokerCache\Event\CacheEvent;
use StrokerCache\Strategy\AbstractStrategy;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;

class AbstractStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestStrategy
     */
    protected $strategy;

    public function setUp()
    {
        $this->strategy = new TestStrategy();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testCorrectListenerIsAttached()
    {
        $eventManagerMock = Mockery::mock(EventManagerInterface::class)
            ->shouldReceive('attach')
            ->once()
            ->with(CacheEvent::EVENT_SHOULDCACHE, array($this->strategy, 'shouldCacheCallback'), 100)
            ->getMock();

        $this->strategy->attach($eventManagerMock);
    }

    public function testListenersCanBeDetached()
    {
        $eventManagerMock = Mockery::mock(EventManagerInterface::class)
            ->shouldReceive('attach')
            ->andReturn(function() {})
            ->getMock();

        $eventManagerMock
            ->shouldReceive('detach')
            ->andReturn(true)
            ->once();

        $this->strategy->attach($eventManagerMock);
        $this->strategy->detach($eventManagerMock);
    }

    public function testShouldCacheIsCalledOnConcreteClasses()
    {
        $mvcEvent = new MvcEvent();
        $event = new CacheEvent();
        $event->setMvcEvent($mvcEvent);
        $this->strategy->shouldCacheCallback($event);

        $this->assertTrue($this->strategy->shouldCacheCalled);
        $this->assertSame($this->strategy->mvcEvent, $mvcEvent);
    }
}

class TestStrategy extends AbstractStrategy
{
    /** @var MvcEvent */
    public $mvcEvent = null;

    public $shouldCacheCalled = false;

    /**
     * {@inheritDoc}
     */
    public function shouldCache(MvcEvent $event)
    {
        $this->mvcEvent = $event;
        $this->shouldCacheCalled = true;
    }
}