<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Service;

use Zend\Mvc\MvcEvent;
use StrokerCache\Options\ModuleOptions;
use Zend\Cache\Storage\TaggableInterface;
use StrokerCache\Strategy\StrategyInterface;
use Zend\Cache\Storage\StorageInterface;

class CacheService
{
    const TAG_PREFIX = 'strokercache_';

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
            return $this->getCacheStorage()->getItem($id);
        }

        return null;
    }

    /**
     * Save the page contents to the cache storage.
     */
    public function save(MvcEvent $e)
    {
        $shouldCache = false;
        $tags = array();
        /** @var $strategy \StrokerCache\Strategy\StrategyInterface */
        foreach ($this->getStrategies() as $strategy) {
            if ($strategy->shouldCache($e)) {
                $shouldCache = true;
                if ($this->getCacheStorage() instanceof TaggableInterface) {
                    $tags = array_merge($tags, $this->getTags($e));
                }
            }
        }

        if ($shouldCache) {
            $id = $this->createId();
            $content = $e->getResponse()->getContent();
            $this->getCacheStorage()->setItem($id, $content);
            if ($this->getCacheStorage() instanceof TaggableInterface) {
                $this->getCacheStorage()->setTags($id, $tags);
            }
        }
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
            function ($tag) { return self::TAG_PREFIX . $tag; },
            $tags
        );
        return $this->getCacheStorage()->clearByTags($tags);
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
     */
    public function setStrategies($strategies)
    {
        $this->strategies = $strategies;
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
     */
    public function setCacheStorage($cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
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
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
}
