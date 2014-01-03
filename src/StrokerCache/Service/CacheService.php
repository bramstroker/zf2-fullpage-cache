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
        if (!$this->getCacheStorage()->hasItem($id)) {
            return null;
        };

        $event = $this->createCacheEvent(CacheEvent::EVENT_LOAD);
        $event->setCacheKey($id);

        $result = $this->getEventManager()->triggerUntil($event, function($result) {
            return $result === false;
        });

        if ($result === false) {
            return null;
        }

        return $this->getCacheStorage()->getItem($id);
    }

    /**
     * Save the page contents to the cache storage.
     */
    public function save(MvcEvent $mvcEvent)
    {
        if (!$this->shouldCacheRequest($mvcEvent)) {
            return;
        }

        $id = $this->createId();

        $item = ($this->getOptions()->getCacheResponse() === true) ? serialize($mvcEvent->getResponse()) : $mvcEvent->getResponse()->getContent();

        $this->getCacheStorage()->setItem($id, $item);

        $this->getEventManager()->trigger($this->createCacheEvent(CacheEvent::EVENT_SAVE, $mvcEvent));

        if ($this->getCacheStorage() instanceof TaggableInterface) {
            $this->getCacheStorage()->setTags($id, $this->getTags($mvcEvent));
        }
    }

    /**
     * Determine if we should cache the current request
     *
     * @param MvcEvent $mvcEvent
     * @return bool
     */
    protected function shouldCacheRequest(MvcEvent $mvcEvent)
    {
        $event = $this->createCacheEvent(CacheEvent::EVENT_SHOULDCACHE, $mvcEvent);

        return $this->getEventManager()->triggerUntil($event, function($result) {
            return $result;
        });
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
     * @param string $eventName
     * @param MvcEvent|null $mvcEvent
     * @return CacheEvent
     */
    protected function createCacheEvent($eventName, MvcEvent $mvcEvent = null)
    {
        $cacheEvent = new CacheEvent($eventName, $this);
        $cacheEvent->setMvcEvent($mvcEvent);
        return $cacheEvent;
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
