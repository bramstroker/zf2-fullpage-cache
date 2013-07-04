<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Listener;

use StrokerCache\Service\CacheService;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Mvc\MvcEvent;

class CacheListener extends AbstractListenerAggregate
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
     * @param CacheService $cacheService
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('route', array($this, 'onRoute'), 100);
        $this->listeners[] = $events->attach('finish', array($this, 'onFinish'), -100);
    }

    /**
     * Load the page contents from the cache and set the response.
     *
     * @param  MvcEvent $e
     * @return \Zend\Stdlib\ResponseInterface|void
     */
    public function onRoute(MvcEvent $e)
    {
        if (!$e->getRequest() instanceof HttpRequest) {
            return;
        }

        $data = $this->getCacheService()->load();

        if ($data !== null) {
            $this->loadedFromCache = true;

            $response = unserialize($data);

            return $response;
        }
    }

    /**
     * Save page contents to the cache
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function onFinish(MvcEvent $e)
    {
        if (!$e->getRequest() instanceof HttpRequest || $this->loadedFromCache) {
            return;
        }

        $this->getCacheService()->save($e);
    }

    /**
     * @return CacheService
     */
    public function getCacheService()
    {
        return $this->cacheService;
    }
}
