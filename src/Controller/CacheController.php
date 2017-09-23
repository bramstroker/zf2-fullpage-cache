<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Controller;

use StrokerCache\Exception\UnsupportedAdapterException;
use StrokerCache\Service\CacheService;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

class CacheController extends AbstractConsoleController
{
    /**
     * @var CacheService
     */
    protected $cacheService;

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
        $tags = $this->params('tags', null);
        if (null === $tags) {
            $this->console->writeLine('You should provide tags');
            return;
        }

        $tags   = explode(',', $tags);
        $result = false;
        try {
            $result = $this->getCacheService()->clearByTags($tags);
        } catch (UnsupportedAdapterException $exception) {
            $this->console->writeLine($exception->getMessage());
        }

        $this->console->writeLine(
            sprintf(
                'Cache invalidation %s',
                $result ? 'succeeded' : 'failed'
            )
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
