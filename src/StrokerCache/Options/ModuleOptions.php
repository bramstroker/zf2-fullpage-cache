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
    protected $strategies;

    /**
     * @var array
     */
    protected $storageAdapter;

    /**
     * @var bool
     */
    protected $cacheResponse = true;

    /**
     * @var string
     */
    protected $idGenerator = 'requesturi';

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

    /**
     * @return string
     */
    public function getIdGenerator()
    {
        return $this->idGenerator;
    }

    /**
     * @param string $idGenerator
     */
    public function setIdGenerator($idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }
}
