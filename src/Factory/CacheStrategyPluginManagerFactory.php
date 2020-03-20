<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use Interop\Container\ContainerInterface;
use StrokerCache\Strategy\CacheStrategyPluginManager;
use Laminas\ServiceManager\Config as ServiceManagerConfig;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CacheStrategyPluginManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        return new CacheStrategyPluginManager(
            $container,
            $config['strokercache']['strategies']['plugin_manager']
        );
    }
}
