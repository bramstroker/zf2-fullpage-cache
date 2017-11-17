<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Service;

use StrokerCache\Exception\UnsupportedAdapterException;
use StrokerCache\IdGenerator\IdGeneratorInterface;
use Zend\Mvc\MvcEvent;
use StrokerCache\Event\CacheEvent;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use StrokerCache\Options\ModuleOptions;
use Zend\Cache\Storage\TaggableInterface;
use Zend\Cache\Storage\StorageInterface;

class CacheService
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
    protected $cacheStorage;

    /**
     * @var IdGeneratorInterface
     */
    protected $idGenerator;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * Default constructor
     *
     * @param StorageInterface $cacheStorage
     * @param ModuleOptions $options
     * @param IdGeneratorInterface $idGenerator
     */
    public function __construct(StorageInterface $cacheStorage, ModuleOptions $options, IdGeneratorInterface $idGenerator = null)
    {
        $this->setCacheStorage($cacheStorage);
        $this->setOptions($options);
        $this->setIdGenerator($idGenerator);
    }

    /**
     * Check if a page is saved in the cache and return contents. Return null when no item is found.
     *
     * @param MvcEvent $mvcEvent
     * @return mixed|null
     */
    public function load(MvcEvent $mvcEvent)
    {
        $id = $this->getIdGenerator()->generate();
        if (!$this->getCacheStorage()->hasItem($id)) {
            return null;
        };

        $event = $this->createCacheEvent(CacheEvent::EVENT_LOAD, $mvcEvent, $id);

        $results = $this->getEventManager()->triggerEventUntil(function ($result) {
            return ($result === false);
        }, $event);

        if ($results->stopped()) {
            return null;
        }

        return $this->getCacheStorage()->getItem($id);
    }

    /**
     * Save the page contents to the cache storage.
     *
     * @param MvcEvent $mvcEvent
     */
    public function save(MvcEvent $mvcEvent)
    {
        if (!$this->shouldCacheRequest($mvcEvent)) {
            return;
        }

        $id = $this->getIdGenerator()->generate();

        $item = ($this->getOptions()->getCacheResponse() === true) ? serialize($mvcEvent->getResponse()) : $mvcEvent->getResponse()->getContent();

        $this->getCacheStorage()->setItem($id, $item);

        $cacheEvent = $this->createCacheEvent(CacheEvent::EVENT_SAVE, $mvcEvent, $id);
        $this->getEventManager()->triggerEvent($cacheEvent);

        $cacheStorage = $this->getCacheStorage();
        if ($cacheStorage instanceof TaggableInterface) {
            $tags = array_unique(array_merge($this->getTags($mvcEvent), $cacheEvent->getTags()));
            $cacheStorage->setTags($id, $tags);
        }
    }

    /**
     * Determine if we should cache the current request
     *
     * @param  MvcEvent $mvcEvent
     * @return bool
     */
    protected function shouldCacheRequest(MvcEvent $mvcEvent)
    {
        $event = $this->createCacheEvent(CacheEvent::EVENT_SHOULDCACHE, $mvcEvent);

        $results = $this->getEventManager()->triggerEventUntil(function ($result) {
            return $result;
        }, $event);

        if ($results->stopped()) {
            return $results->last();
        }

        return false;
    }

    /**
     * @param  array $tags
     * @param  bool|null $disjunction
     * @return bool
     * @throws UnsupportedAdapterException
     */
    public function clearByTags(array $tags = array(), $disjunction = null)
    {
        $cacheStorage = $this->getCacheStorage();
        if (!$cacheStorage instanceof TaggableInterface) {
            throw new UnsupportedAdapterException('purging by tags is only supported on adapters implementing the TaggableInterface');
        }
        $tags = array_map(
            function ($tag) { return CacheService::TAG_PREFIX . $tag; },
            $tags
        );

        return $cacheStorage->clearByTags($tags, $disjunction);
    }

    /**
     * Cache tags to use for this page
     *
     * @param  MvcEvent $event
     * @return array
     */
    public function getTags(MvcEvent $event)
    {
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $tags = [
            self::TAG_PREFIX . 'route_' . $routeName
        ];
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
     * @param  string $eventName
     * @param  string $cacheKey
     * @param  MvcEvent|null $mvcEvent
     * @return CacheEvent
     */
    protected function createCacheEvent($eventName, MvcEvent $mvcEvent = null, $cacheKey = null)
    {
        $cacheEvent = new CacheEvent($eventName, $this);
        $cacheEvent->setCacheKey($cacheKey);
        if ($mvcEvent !== null) {
            $cacheEvent->setMvcEvent($mvcEvent);
        }

        return $cacheEvent;
    }

    /**
     * @return StorageInterface
     */
    public function getCacheStorage()
    {
        return $this->cacheStorage;
    }

    /**
     * @param  StorageInterface $cacheStorage
     * @return self
     */
    public function setCacheStorage($cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;

        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param  ModuleOptions $options
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
        $eventManager->setIdentifiers([__CLASS__, get_called_class()]);

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

    /**
     * @return IdGeneratorInterface
     */
    public function getIdGenerator()
    {
        return $this->idGenerator;
    }

    /**
     * @param IdGeneratorInterface $idGenerator
     */
    public function setIdGenerator($idGenerator = null)
    {
        $this->idGenerator = $idGenerator;
    }
}
