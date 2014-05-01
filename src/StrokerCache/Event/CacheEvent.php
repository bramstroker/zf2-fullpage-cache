<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Event;

use Zend\EventManager\Event;
use Zend\Mvc\MvcEvent;

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

}
