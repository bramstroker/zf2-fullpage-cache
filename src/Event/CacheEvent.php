<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Event;

use Laminas\EventManager\Event;
use Laminas\Mvc\MvcEvent;

class CacheEvent extends Event
{
    const EVENT_SAVE = 'save';
    const EVENT_LOAD = 'load';
    const EVENT_SHOULDCACHE = 'shouldCache';

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * @param string $cacheKey
     * @return CacheEvent
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

    /**
     * @param MvcEvent $mvcEvent
     * @return CacheEvent
     */
    public function setMvcEvent($mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }
}
