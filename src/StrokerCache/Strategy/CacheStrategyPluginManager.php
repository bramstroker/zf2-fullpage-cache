<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use StrokerCache\Exception;
use Zend\ServiceManager\AbstractPluginManager;

class CacheStrategyPluginManager extends AbstractPluginManager
{
    /**
     * {@inheritdoc}
     */
    public function validate($instance)
    {
        if ($instance instanceof StrategyInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidStrategyException(sprintf(
            'Plugin of type %s is invalid; must implement %s\StrategyInterface',
            (is_object($instance) ? get_class($instance) : gettype($instance)),
            __NAMESPACE__
        ));
    }

    /**
     * @deprecated to support ServiceManager v2
     */
    public function validatePlugin($instance)
    {
        $this->validate($instance);
    }
}
