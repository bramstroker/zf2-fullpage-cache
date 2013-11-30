<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Event;

use Zend\EventManager\Event;

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
     * @var bool
     */
    protected $abort = false;

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * @param string $cacheKey
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @return boolean
     */
    public function getAbort()
    {
        return $this->abort;
    }

    /**
     * @param boolean $abort
     * @return CacheEvent
     */
    public function setAbort($abort)
    {
        $this->abort = (boolean) $abort;
        return $this;
    }
}
