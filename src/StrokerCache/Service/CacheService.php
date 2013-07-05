<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Service;

use Zend\Mvc\MvcEvent;
use StrokerCache\Event\CacheEvent;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use StrokerCache\Options\ModuleOptions;
use Zend\Cache\Storage\TaggableInterface;
use StrokerCache\Strategy\StrategyInterface;
use Zend\Cache\Storage\StorageInterface;

class CacheService implements EventManagerAwareInterface
{
    /**
     * Prefix to use for the tag key
     * @var string
     */
    const TAG_PREFIX = 'strokercache_';

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var StorageInterface
     */
    private $cacheStorage;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var array
     */
    protected $strategies = array();

    /**
     * Default constructor
     *
     * @param \Zend\Cache\Storage\StorageInterface $cacheStorage
     * @param \StrokerCache\Options\ModuleOptions  $options
     */
    public function __construct(StorageInterface $cacheStorage, ModuleOptions $options)
    {
        $this->setCacheStorage($cacheStorage);
        $this->setOptions($options);
    }

    /**
     * Check if a page is saved in the cache and return contents. Return null when no item is found.
     */
    public function load()
    {
        $id = $this->createId();
        if ($this->getCacheStorage()->hasItem($id)) {
            $event = new CacheEvent(CacheEvent::EVENT_LOAD, $this);
            $event->setCacheKey($id);
            $this->getEventManager()->trigger($event);
            return $this->getCacheStorage()->getItem($id);
        }

        return null;
    }

    /**
     * Save the page contents to the cache storage.
     */
    public function save(MvcEvent $e)
    {
        if (!$this->shouldCacheRequest($e)) {
            return;
        }

        $id = $this->createId();

        $this->getCacheStorage()->setItem($id, serialize($e->getResponse()));

        $this->getEventManager()->trigger(new CacheEvent(CacheEvent::EVENT_SAVE, $this));

        if ($this->getCacheStorage() instanceof TaggableInterface) {
            $this->getCacheStorage()->setTags($id, $this->getTags($e));
        }
    }

    /**
     * Determine if we should cache the current request
     *
     * @param MvcEvent $e
     * @return bool
     */
    protected function shouldCacheRequest(MvcEvent $e)
    {
        /** @var $strategy \StrokerCache\Strategy\StrategyInterface */
        foreach ($this->getStrategies() as $strategy) {
            if ($strategy->shouldCache($e)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine the page to save from the request
     *
     * @throws \RuntimeException
     * @return string
     */
    protected function createId()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            throw new \RuntimeException("Can't auto-detect current page identity");
        }

        $requestUri = $_SERVER['REQUEST_URI'];

        return md5($requestUri);
    }

    /**
     * @param array $tags
     * @return bool
     */
    public function clearByTags(array $tags = array())
    {
        if (!$this->getCacheStorage() instanceof TaggableInterface) {
            return false;
        }
        $tags = array_map(
            function ($tag) { return CacheService::TAG_PREFIX . $tag; },
            $tags
        );
        return $this->getCacheStorage()->clearByTags($tags);
    }

    /**
     * Cache tags to use for this page
     *
     * @param  \Zend\Mvc\MvcEvent $event
     * @return array
     */
    public function getTags(MvcEvent $event)
    {
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $tags = array(
            self::TAG_PREFIX . 'route_' . $routeName
        );
        foreach ($event->getRouteMatch()->getParams() as $key => $value) {
            if ($key == 'controller') {
                $tags[] = self::TAG_PREFIX . 'controller_' . $value;
            } else {
                $tags[] = self::TAG_PREFIX . 'param_' . $key . '_' . $value;
            }
        }

        return $tags;
    }

    /**
     * @return array
     */
    public function getStrategies()
    {
        return $this->strategies;
    }

    /**
     * @param array $strategies
     * @return self
     */
    public function setStrategies($strategies)
    {
        $this->strategies = $strategies;
        return $this;
    }

    /**
     * @param \StrokerCache\Strategy\StrategyInterface $strategy
     */
    public function addStrategy(StrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * @return \Zend\Cache\Storage\StorageInterface
     */
    public function getCacheStorage()
    {
        return $this->cacheStorage;
    }

    /**
     * @param \Zend\Cache\Storage\StorageInterface $cacheStorage
     * @return self
     */
    public function setCacheStorage($cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
        return $this;
    }

    /**
     * @return \StrokerCache\Options\ModuleOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param \StrokerCache\Options\ModuleOptions $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return self
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_called_class()
        ));

        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }
}
