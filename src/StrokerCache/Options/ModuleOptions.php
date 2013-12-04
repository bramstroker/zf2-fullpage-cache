<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Options;

use Zend\Stdlib\AbstractOptions;
use StrokerCache\Exception\InvalidArgumentException;
use StrokerCache\Strategy\StrategyInterface;

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
     * @param $strategy
     * @return array
     */
    public function getStrategyOptions($strategy)
    {
        if ($strategy instanceof StrategyInterface) {
            $strategy = get_class($strategy);
        }

        if (!is_string($strategy)) {
            throw new InvalidArgumentException('Strategy should be eighter a string or implement the StrategyInterface');
        }

        if (!isset($this->strategies['enabled'][$strategy])) {
            return array();
        }

        return $this->strategies['enabled'][$strategy];
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
}
