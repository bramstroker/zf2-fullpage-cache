<?php
return array(
    'strokercache' => array(
        'storage_adapter' => array(
            'name' => 'Zend\Cache\Storage\Adapter\FileSystem'
        ),
        'strategies' => array(
            'routeName' => array(
                'routes' => array(
                    'home',
                    'leaderboard',
                    'match/list'
                )
            ),
            /*'controllerName' => array(
                'controllers' => array(
                    'StrokerToto\Controller\User'
                )
            ),
            'url' => array(
                'regexpes' => array(
                    '/^\/user/'
                )
            )*/
        )
    )
);