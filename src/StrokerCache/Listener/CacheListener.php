<?php
/*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
* This software consists of voluntary contributions made by many individuals
* and is licensed under the MIT license.
*/

namespace StrokerCache\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\Cache\Storage\TaggableInterface;
use StrokerCache\Strategy\StrategyInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use StrokerCache\Options\ModuleOptions;

class CacheListener implements ListenerAggregateInterface
{
    /**
     * @var StorageInterface
     */
    protected $cache;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var array
     */
    protected $strategies = array();

    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * Default constructor
     *
     * @param StorageInterface $cache
     * @param ModuleOptions $options
     */
    public function __construct(StorageInterface $cache, ModuleOptions $options)
    {
        $this->cache = $cache;
        $this->options = $options;
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
        $this->listeners[] = $events->attach('route', array($this, 'load'), 100);
        $this->listeners[] = $events->attach('finish', array($this, 'save'), -100);
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
     * @param \Zend\Mvc\MvcEvent $e
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function load(MvcEvent $e)
    {
        $id = $this->createId();
        if ($this->cache->hasItem($id)) {
            $response = $e->getResponse();
            $content = $this->cache->getItem($id);
            $response->setContent($content);
            return $response;
        }
    }

    /**
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function save(MvcEvent $e)
    {
        $id = $this->createId();
        if (!$this->cache->hasItem($id)) {
            $shouldCache = false;
            $tags = array();
            /** @var $strategy \StrokerCache\Strategy\StrategyInterface */
            foreach ($this->getStrategies() as $strategy) {
                if ($strategy->shouldCache($e)) {
                    $shouldCache = true;
                    $tags = array_merge($tags, $strategy->getTags($e));
                }
            }

            if ($shouldCache) {
                $content = $e->getResponse()->getContent();
                $this->cache->setItem($id, $content);
                if ($this->cache instanceof TaggableInterface) {
                    $this->cache->setTags($id, $tags);
                }
            }
        }
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
     * Make an id depending on REQUEST_URI and superglobal arrays (depending on options)
     *
     * @return mixed|false a cache id (string), false if the cache should have not to be used
     */
    protected function makeId()
    {
        $tmp = $_SERVER['REQUEST_URI'];
        $array = explode('?', $tmp, 2);
        $tmp = $array[0];
        foreach (array('Get', 'Post', 'Session', 'Files', 'Cookie') as $arrayName) {
            $tmp2 = $this->makePartialId($arrayName, true, true);
            if ($tmp2===false) {
                return false;
            }
            $tmp = $tmp . $tmp2;
        }
        return md5($tmp);
    }

    /**
     * Make a partial id depending on options
     *
     * @param  string $arrayName Superglobal array name
     * @param  bool   $bool1     If true, cache is still on even if there are some variables in the superglobal array
     * @param  bool   $bool2     If true, we have to use the content of the superglobal array to make a partial id
     * @return mixed|false Partial id (string) or false if the cache should have not to be used
     */
    protected function makePartialId($arrayName, $bool1, $bool2)
    {
        switch ($arrayName) {
            case 'Get':
                $var = $_GET;
                break;
            case 'Post':
                $var = $_POST;
                break;
            case 'Session':
                if (isset($_SESSION)) {
                    $var = $_SESSION;
                } else {
                    $var = null;
                }
                break;
            case 'Cookie':
                if (isset($_COOKIE)) {
                    $var = $_COOKIE;
                } else {
                    $var = null;
                }
                break;
            case 'Files':
                $var = $_FILES;
                break;
            default:
                return false;
        }
        if ($bool1) {
            if ($bool2) {
                return serialize($var);
            }
            return '';
        }
        if (count($var) > 0) {
            return false;
        }
        return '';
    }

    /**
     * @return \StrokerCache\Options\ModuleOptions
     */
    public function getOptions()
    {
        return $this->options;
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
}
