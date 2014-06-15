<?php
/**
 * @author        Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license       http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use StrokerCache\Exception\RuntimeException;
use StrokerCache\Listener\ShouldCacheStrategyListener;
use StrokerCache\Options\ModuleOptions;
use StrokerCache\Service\CacheService;
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
        $options      = $serviceLocator->get('StrokerCache\Options\ModuleOptions');
        $cacheStorage = $serviceLocator->get('StrokerCache\Storage\CacheStorage');

        $cacheService = new CacheService($cacheStorage, $options);

        $this->setupIdGenerator($cacheService, $options);
        $this->attachStrategiesToEventManager($cacheService, $options, $serviceLocator);

        return $cacheService;
    }

    /**
     * @param CacheService            $cacheService
     * @param ModuleOptions           $options
     * @param ServiceLocatorInterface $serviceLocator
     * @throws RuntimeException
     */
    protected function setupIdGenerator(
        CacheService $cacheService,
        ModuleOptions $options,
        ServiceLocatorInterface $serviceLocator
    ) {
        $idGenerator        = $options->getIdGenerator();
        $idGeneratorManager = $serviceLocator->get('StrokerCache\IdGenerator\IdGeneratorPluginManager');

        if ($idGeneratorManager->has($idGenerator)) {
            $cacheService->setIdGenerator($idGeneratorManager->get($idGenerator));
        } else {
            throw new RuntimeException('No IdGenerator register for key ' . $idGenerator);
        }
    }

    /**
     * @param CacheService            $cacheService
     * @param ModuleOptions           $options
     * @param ServiceLocatorInterface $serviceLocator
     */
    protected function attachStrategiesToEventManager(
        CacheService $cacheService,
        ModuleOptions $options,
        ServiceLocatorInterface $serviceLocator
    ) {
        // Register enabled strategies on the cacheListener
        $strategies = $options->getStrategies();
        if (isset($strategies['enabled'])) {
            /** @var $strategyPluginManager \StrokerCache\Strategy\CacheStrategyPluginManager */
            $strategyPluginManager = $serviceLocator->get('StrokerCache\Strategy\CacheStrategyPluginManager');

            foreach ($strategies['enabled'] as $alias => $options) {
                if (is_numeric($alias)) {
                    $alias = $options;
                }
                $strategy = $strategyPluginManager->get($alias, $options);

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
