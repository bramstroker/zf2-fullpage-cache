<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Listener;

use StrokerCache\Options\ModuleOptions;
use StrokerCache\Service\CacheService;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
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
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var bool
     */
    protected $loadedFromCache = false;

    /**
     * Default constructor
     *
     * @param CacheService $cacheService
     * @param ModuleOptions $options
     */
    public function __construct(CacheService $cacheService, ModuleOptions $options)
    {
        $this->cacheService = $cacheService;
        $this->options      = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach('route', [$this, 'onRoute'], 100);
        $this->listeners[] = $events->attach('finish', [$this, 'onFinish'], -100);
    }

    /**
     * Load the page contents from the cache and set the response.
     *
     * @param  MvcEvent $e
     * @return HttpResponse
     */
    public function onRoute(MvcEvent $e)
    {
        if (!$e->getRequest() instanceof HttpRequest || !$e->getResponse() instanceof HttpResponse) {
            return null;
        }

        /** @var HttpResponse $response */
        $response = $e->getResponse();

        $data = $this->getCacheService()->load($e);

        if ($data !== null) {
            $this->loadedFromCache = true;

            if ($this->getOptions()->getCacheResponse() === true) {
                $response = unserialize($data);
            } else {
                $response = $e->getResponse();
                $response->setContent($data);
            }

            $this->addDebugResponseHeader($response, 'Hit');

            return $response;
        }

        $this->addDebugResponseHeader($response, 'Miss');
    }

    /**
     * @param HttpResponse $response
     * @param string $value
     */
    private function addDebugResponseHeader(HttpResponse $response, $value)
    {
        if ($this->getOptions()->isAddDebugHeaders()) {
            $response->getHeaders()->addHeaderLine('X-Stroker-Cache', $value);
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

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        return $this->options;
    }
}
