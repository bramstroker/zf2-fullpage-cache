<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var array
     */
    private $strategies;

    /**
     * @var array
     */
    private $storageAdapter;

    /**
     * @var bool
     */
    private $cacheResponse = true;


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
    public function setStrategies(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * @return array
     */
    public function getStorageAdapter()
    {
        return $this->storageAdapter;
    }

    /**
     * @param array $storageAdapter
     */
    public function setStorageAdapter(array $storageAdapter)
    {
        $this->storageAdapter = $storageAdapter;
    }

    /**
     * @param boolean $cacheResponse
     */
    public function setCacheResponse($cacheResponse)
    {
        $this->cacheResponse = $cacheResponse;
    }

    /**
     * @return boolean
     */
    public function getCacheResponse()
    {
        return $this->cacheResponse;
    }
}
