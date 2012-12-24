<?php
return array(
    'strokercache' => array(
        'storage_adapter' => array(
            'name' => 'Zend\Cache\Storage\Adapter\FileSystem',
            'options' => array(
                'cache_dir' => __DIR__ . '/../../../data'
            )
        ),
        'strategies' => array(
            'plugin_manager' => array(
                'abstract_factories' => array(
                    'StrokerCache\Strategy\Factory',
                )
            ),
        )
    )
);