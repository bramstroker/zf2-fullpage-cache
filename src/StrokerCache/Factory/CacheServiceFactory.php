<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use StrokerCache\Exception\RuntimeException;
use StrokerCache\Listener\ShouldCacheStrategyListener;
use StrokerCache\Options\ModuleOptions;
use StrokerCache\Service\CacheService;
use StrokerCache\IdGenerator\IdGeneratorPluginManager;
use Zend\EventManager\ListenerAggregateInterface;
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

        $idGenerator = $options->getIdGenerator();
        if (!empty($idGenerator)) {
            $idGeneratorHelper = new IdGeneratorPluginManager();
            if ($idGeneratorHelper->has($idGenerator)) {
                $cacheService->setIdGenerator($idGeneratorHelper->get($idGenerator));
            } else {
                throw new RuntimeException('No IdGenerator register for key ' . $idGenerator);
            }
        }

        $this->attachStrategiesToEventManager($cacheService, $options, $serviceLocator);

        return $cacheService;
    }

    /**
     * @param CacheService $cacheService
     * @param ModuleOptions $options
     * @param ServiceLocatorInterface $serviceLocator
     */
    protected function attachStrategiesToEventManager(CacheService $cacheService, ModuleOptions $options, ServiceLocatorInterface $serviceLocator)
    {
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

                if ($strategy instanceof ListenerAggregateInterface) {
                    $listener = $strategy;
                } else {
                    $listener = new ShouldCacheStrategyListener($strategy);
                }
                $cacheService->getEventManager()->attach($listener);
            }
        }
    }
}
