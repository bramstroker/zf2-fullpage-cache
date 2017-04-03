<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Listener;

use StrokerCache\Event\CacheEvent;
use StrokerCache\Strategy\StrategyInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class ShouldCacheStrategyListener extends AbstractListenerAggregate
{
    /**
     * @var StrategyInterface
     */
    protected $strategy;

    /**
     * @param StrategyInterface $strategy
     */
    public function __construct(StrategyInterface $strategy)
    {
        $this->setStrategy($strategy);
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(CacheEvent::EVENT_SHOULDCACHE, [$this, 'shouldCache'], 100);
    }

    /**
     * @param  CacheEvent $event
     * @return bool
     */
    public function shouldCache(CacheEvent $event)
    {
        return $this->getStrategy()->shouldCache($event->getMvcEvent());
    }

    /**
     * @param StrategyInterface $strategy
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @return StrategyInterface
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}
