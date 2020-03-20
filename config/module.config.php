<?php
use StrokerCache\Controller\CacheController;
use StrokerCache\Factory\CacheControllerFactory;
use StrokerCache\Factory\CacheListenerFactory;
use StrokerCache\Factory\CacheServiceFactory;
use StrokerCache\Factory\CacheStorageFactory;
use StrokerCache\Factory\CacheStrategyPluginManagerFactory;
use StrokerCache\Factory\IdGeneratorPluginManagerFactory;
use StrokerCache\Factory\ModuleOptionsFactory;
use StrokerCache\IdGenerator\IdGeneratorPluginManager;
use StrokerCache\Listener\CacheListener;
use StrokerCache\Options\ModuleOptions;
use StrokerCache\Service\CacheService;
use StrokerCache\Strategy\CacheAllExcept;
use StrokerCache\Strategy\CacheStrategyPluginManager;
use StrokerCache\Strategy\Controller;
use StrokerCache\Strategy\Route;
use StrokerCache\Strategy\UriPath;
use Laminas\Cache\Storage\Adapter\Filesystem;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'factories' => [
            CacheListener::class => CacheListenerFactory::class,
            ModuleOptions::class => ModuleOptionsFactory::class,
            CacheService::class => CacheServiceFactory::class,
            'StrokerCache\Storage\CacheStorage' => CacheStorageFactory::class,
            CacheStrategyPluginManager::class => CacheStrategyPluginManagerFactory::class,
            IdGeneratorPluginManager::class => IdGeneratorPluginManagerFactory::class,
        ],
        'aliases'   => [
            'strokercache_service' => CacheService::class
        ]
    ],
    'console' => [
        'router' => [
            'routes' => [
                'strokercache-clear' => [
                    'options' => [
                        'route'    => 'strokercache clear <tags>',
                        'defaults' => [
                            'controller' => CacheController::class,
                            'action'     => 'clear',
                        ]
                    ],
                ],
            ],
        ],
    ],
    'controllers'     => [
        'factories' => [
            CacheController::class => CacheControllerFactory::class
        ]
    ],
    'strokercache'    => [
        'storage_adapter' => [
            'name' => Filesystem::class,
        ],
        'id_generators'   => [
            'plugin_manager' => []
        ],
        'strategies'      => [
            'plugin_manager' => [
                'factories' => [
                    CacheAllExcept::class => InvokableFactory::class,
                    Controller::class => InvokableFactory::class,
                    Route::class => InvokableFactory::class,
                    UriPath::class => InvokableFactory::class
                ],
            ],
        ],
        'id_generator'    => 'requestUri'
    ],
];
