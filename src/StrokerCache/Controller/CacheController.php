<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Controller;

use StrokerCache\Service\CacheService;
use Zend\Console\Adapter\AdapterInterface as Console;
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
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Clear items from the cache
     *
     * @return string
     */
    public function clearAction()
    {
        $tags = $this->getRequest()->getParam('tags');
        if (null === $tags) {
            return "\n\nYou should provide tags";
        }

        $tags   = explode(',', $tags);
        $result = $this->getCacheService()->clearByTags($tags);

        return sprintf(
            "\n\nCache invalidation %s\n\n",
            $result ? 'succeeded' : 'failed'
        );
    }

    /**
     * Get the cache service
     *
     * @return CacheService
     */
    public function getCacheService()
    {
        return $this->cacheService;
    }
}
