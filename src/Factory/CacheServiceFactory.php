<?php
/**
 * @author        Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license       http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use Interop\Container\ContainerInterface;
use StrokerCache\Exception\RuntimeException;
use StrokerCache\IdGenerator\IdGeneratorPluginManager;
use StrokerCache\Listener\ShouldCacheStrategyListener;
use StrokerCache\Options\ModuleOptions;
use StrokerCache\Service\CacheService;
use StrokerCache\Strategy\CacheStrategyPluginManager;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CacheServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options      = $container->get(ModuleOptions::class);
        $cacheStorage = $container->get('StrokerCache\Storage\CacheStorage');

        $cacheService = new CacheService($cacheStorage, $options);

        $this->setupIdGenerator($cacheService, $options, $container);
        $this->attachStrategiesToEventManager($cacheService, $options, $container);

        return $cacheService;
    }

    /**
     * @param CacheService            $cacheService
     * @param ModuleOptions           $options
     * @param ContainerInterface      $container
     * @throws RuntimeException
     */
    protected function setupIdGenerator(
        CacheService $cacheService,
        ModuleOptions $options,
        ContainerInterface $container
    ) {
        $idGenerator        = $options->getIdGenerator();
        $idGeneratorManager = $container->get(IdGeneratorPluginManager::class);

        if ($idGeneratorManager->has($idGenerator)) {
            $cacheService->setIdGenerator($idGeneratorManager->get($idGenerator));
        } else {
            throw new RuntimeException('No IdGenerator register for key ' . $idGenerator);
        }
    }

    /**
     * @param CacheService            $cacheService
     * @param ModuleOptions           $options
     * @param ContainerInterface      $container
     */
    protected function attachStrategiesToEventManager(
        CacheService $cacheService,
        ModuleOptions $options,
        ContainerInterface $container
    ) {
        // Register enabled strategies on the cacheListener
        $strategies = $options->getStrategies();
        if (isset($strategies['enabled'])) {
            /** @var $strategyPluginManager CacheStrategyPluginManager */
            $strategyPluginManager = $container->get(CacheStrategyPluginManager::class);

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
                $listener->attach($cacheService->getEventManager());
            }
        }
    }
}
