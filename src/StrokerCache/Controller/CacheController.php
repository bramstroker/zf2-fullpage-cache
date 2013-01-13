<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Controller;

use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\Request as ConsoleRequest;
use StrokerCache\Exception\RuntimeException;
use StrokerCache\Service\CacheService;
use Zend\Mvc\Controller\AbstractActionController;

class CacheController extends AbstractActionController
{
    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @var Console
     */
    protected $console;

    /**
     * Default constructor
     *
     * @param CacheService $cacheService
     */
    public function __construct(CacheService $cacheService, Console $console)
    {
        $this->setCacheService($cacheService);
        $this->setConsole($console);
    }

    /**
     * Clear items from the cache
     */
    public function clearAction()
    {
        $this->guardConsole();
        $tags = $this->getRequest()->getParam('tags');
        if ($tags === null) {
            $this->getConsole()->writeLine('You should provide tags');
            return;
        }

        $tags = explode(',', $tags);
        $result = $this->getCacheService()->clearByTags($tags);
        $this->getConsole()->writeLine('Cache invalidation ' . $result ? 'succesfull' : 'failed');
    }

    /**
     * Make sure actions in this controller are only runned from the console
     */
    protected function guardConsole()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException(sprintf(
                '%s can only be run from the console',
                __METHOD__
            ));
        }
    }

    /**
     * @return \StrokerCache\Service\CacheService
     */
    public function getCacheService()
    {
        return $this->cacheService;
    }

    /**
     * @param \StrokerCache\Service\CacheService $cacheService
     */
    public function setCacheService($cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * @return \Zend\Console\Adapter\AdapterInterface
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * @param \Zend\Console\Adapter\AdapterInterface $console
     */
    public function setConsole($console)
    {
        $this->console = $console;
    }
}
