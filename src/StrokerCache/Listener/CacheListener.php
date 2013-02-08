<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use StrokerCache\Service\CacheService;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

class CacheListener implements ListenerAggregateInterface
{
    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @var bool
     */
    protected $loadedFromCache = false;

    /**
     * Default constructor
     *
     * @param \StrokerCache\Service\CacheService $cacheService
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('route', array($this, 'onRoute'), 100);
        $this->listeners[] = $events->attach('finish', array($this, 'onFinish'), -100);
    }

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Load the page contents from the cache and set the response.
     *
     * @param  \Zend\Mvc\MvcEvent             $e
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function onRoute(MvcEvent $e)
    {
        if (!$e->getRequest() instanceof HttpRequest) {
            return;
        }
        $data = $this->getCacheService()->load();
        if ($data !== null) {
            $this->loadedFromCache = true;
            $response = $e->getResponse();
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * Save page contents to the cache
     *
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onFinish(MvcEvent $e)
    {
        if (!$e->getRequest() instanceof HttpRequest || $this->loadedFromCache) {
            return;
        }
        $this->getCacheService()->save($e);
    }

    /**
     * @return \StrokerCache\Service\CacheService
     */
    public function getCacheService()
    {
        return $this->cacheService;
    }
}
