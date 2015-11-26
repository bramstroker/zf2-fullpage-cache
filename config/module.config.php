<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'StrokerCache\Listener\CacheListener'               => 'StrokerCache\Factory\CacheListenerFactory',
            'StrokerCache\Options\ModuleOptions'                => 'StrokerCache\Factory\ModuleOptionsFactory',
            'strokerCache\Service\CacheService'                 => 'StrokerCache\Factory\CacheServiceFactory',
            'StrokerCache\Storage\CacheStorage'                 => 'StrokerCache\Factory\CacheStorageFactory',
            'StrokerCache\Strategy\CacheStrategyPluginManager'  => 'StrokerCache\Factory\CacheStrategyPluginManagerFactory',
            'StrokerCache\IdGenerator\IdGeneratorPluginManager' => 'StrokerCache\Factory\IdGeneratorPluginManagerFactory',
        ),
        'aliases'   => array(
            'strokercache_service' => 'StrokerCache\Service\CacheService'
        )
    ),
    'console'         => array(
        'router' => array(
            'routes' => array(
                'strokercache-clear' => array(
                    'options' => array(
                        'route'    => 'strokercache clear <tags>',
                        'defaults' => array(
                            'controller' => 'StrokerCache\Controller\Cache',
                            'action'     => 'clear',
                        )
                    ),
                ),
            ),
        ),
    ),
    'controllers'     => array(
        'factories' => array(
            'StrokerCache\Controller\Cache' => 'StrokerCache\Factory\CacheControllerFactory'
        )
    ),
    'strokercache'    => array(
        'storage_adapter' => array(
            'name' => 'Zend\Cache\Storage\Adapter\Filesystem',
        ),
        'id_generators'   => array(
            'plugin_manager' => array()
        ),
        'strategies'      => array(
            'plugin_manager' => array(
                'invokables' => array(
                    'StrokerCache\Strategy\CacheAllExcept' => 'StrokerCache\Strategy\CacheAllExcept',
                    'StrokerCache\Strategy\Controller' => 'StrokerCache\Strategy\Controller',
                    'StrokerCache\Strategy\Route'      => 'StrokerCache\Strategy\Route',
                    'StrokerCache\Strategy\UriPath'    => 'StrokerCache\Strategy\UriPath'
                ),
                // This is for BC support
                'aliases'    => array(
                    'StrokerCache\Strategy\RouteName'      => 'StrokerCache\Strategy\Route',
                    'StrokerCache\Strategy\ControllerName' => 'StrokerCache\Strategy\Controller',
                    'StrokerCache\Strategy\Url'            => 'StrokerCache\Strategy\UriPath',
                )
            ),
        ),
        'id_generator'    => 'requestUri'
    ),
);
