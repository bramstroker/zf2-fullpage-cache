<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use StrokerCache\Service\CacheService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $options \StrokerCache\Options\ModuleOptions */
        $options = $serviceLocator->get('StrokerCache\Options\ModuleOptions');

        $cacheStorage = $serviceLocator->get('StrokerCache\Storage\CacheStorage');
        $cacheService = new CacheService($cacheStorage, $options);

        // Register enabled strategies on the cacheListener
        $strategies = $options->getStrategies();
        if (isset($strategies['enabled'])) {
            /** @var $strategyPluginManager \StrokerCache\Strategy\CacheStrategyPluginManager */
            $strategyPluginManager = $serviceLocator->get('StrokerCache\Strategy\CacheStrategyPluginManager');

            foreach ($strategies['enabled'] as $alias => $options) {
                if (is_numeric($alias)) {
                    $alias = $options;
                }
                $strategy = $strategyPluginManager->get($alias);
                $cacheService->addStrategy($strategy);
            }
        }

        return $cacheService;
    }
}
