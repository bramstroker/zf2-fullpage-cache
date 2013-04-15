<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use StrokerCache\Strategy\CacheStrategyPluginManager;
use Zend\ServiceManager\Config as ServiceManagerConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheStrategyPluginManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        return new CacheStrategyPluginManager(
            new ServiceManagerConfig($config['strokercache']['strategies']['plugin_manager'])
        );
    }
}
