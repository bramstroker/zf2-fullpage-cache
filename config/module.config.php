<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'StrokerCache\Listener\CacheListener'              => 'StrokerCache\Factory\CacheListenerFactory',
            'StrokerCache\Options\ModuleOptions'               => 'StrokerCache\Factory\ModuleOptionsFactory',
            'strokerCache\Service\CacheService'                => 'StrokerCache\Factory\CacheServiceFactory',
            'StrokerCache\Storage\CacheStorage'                => 'StrokerCache\Factory\CacheStorageFactory',
            'StrokerCache\Strategy\CacheStrategyPluginManager' => 'StrokerCache\Factory\CacheStrategyPluginManagerFactory',
        ),
        'aliases' => array(
            'strokercache_service' => 'StrokerCache\Service\CacheService'
        )
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'strokercache-clear' => array(
                    'options' => array(
                        'route' => 'strokercache clear <tags>',
                        'defaults' => array(
                            'controller' => 'StrokerCache\Controller\Cache',
                            'action'     => 'clear',
                        )
                    ),
                ),
            ),
        ),
    ),

    'controllers' => array(
        'factories' => array(
            'StrokerCache\Controller\Cache' => 'StrokerCache\Factory\CacheControllerFactory'
        )
    ),

    'strokercache' => array(
        'storage_adapter' => array(
            'name' => 'Zend\Cache\Storage\Adapter\Filesystem',
        ),
        'strategies' => array(
            'plugin_manager' => array(
                'abstract_factories' => array(
                    'StrokerCache\Factory\CacheStrategyAbstractFactory',
                )
            ),
        )
    ),
);
