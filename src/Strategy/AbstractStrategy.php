<?php
/**
 * Created by PhpStorm.
 * User: bram
 * Date: 3-1-14
 * Time: 21:46
 */

namespace StrokerCache\Strategy;

use StrokerCache\Event\CacheEvent;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Stdlib\AbstractOptions;

abstract class AbstractStrategy extends AbstractOptions implements
    ListenerAggregateInterface,
    StrategyInterface
{
    /**
     * @var callable[]
     */
    protected $listeners = [];

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            $events->detach($callback);
            unset($this->listeners[$index]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $listener = 1)
    {
        $this->listeners[] = $events->attach(CacheEvent::EVENT_SHOULDCACHE, [$this, 'shouldCacheCallback'], 100);
    }

    /**
     * @param  CacheEvent $event
     * @return bool
     */
    public function shouldCacheCallback(CacheEvent $event)
    {
        return $this->shouldCache($event->getMvcEvent());
    }
}
