# StrokerCache

[![Build Status](https://travis-ci.org/bramstroker/zf2-fullpage-cache.png?branch=master)](https://travis-ci.org/bramstroker/zf2-fullpage-cache)
[![Coverage Status](https://coveralls.io/repos/bramstroker/zf2-fullpage-cache/badge.png)](https://coveralls.io/r/bramstroker/zf2-fullpage-cache)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/bramstroker/zf2-fullpage-cache/badges/quality-score.png?s=82cfa6f87dbe10c8c9d9e74ca62027a80a8c9cfb)](https://scrutinizer-ci.com/g/bramstroker/zf2-fullpage-cache/)

This module provides a full page cache solution for Zend Framework 2.

## Installation

Installation of StrokerCache uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

  1. `cd my/project/directory`
  2. create or modify the `composer.json` file within your ZF2 application file with
     following contents:

     ```json
     {
         "require": {
             "stroker/cache": "*"
         }
     }
     ```
  3. install composer via `curl -s https://getcomposer.org/installer | php` (on windows, download
     https://getcomposer.org/installer and execute it with PHP). Then run `php composer.phar install`
  4. open `my/project/directory/configs/application.config.php` and add the following key to your `modules`:

     ```php
     'StrokerCache',
     ```

## Setup cache rules

Copy the file `strokercache.local.php.dist` to your config/autoload directory and rename to `strokercache.local.php`. Edit this file to reflect your needs.

The module provides several strategies to determine if a page should be cached.

- By routename
- By controller classname
- By regex on the URI
- Disable caching for authenticated users

### Examples

Caching the home route:

```php
<?php
return array(
    'strokercache' => array(
        'strategies' => array(
            'enabled' => array(
                'StrokerCache\Strategy\RouteName' => array(
                    'routes' => array(
                        'home'
                    ),
                ),
            ),
        ),
    ),
);
```

Caching the `foo/bar` route, but only for a GET request and only when the param `id` equals 60

```php
<?php
return array(
    'strokercache' => array(
        'strategies' => array(
            'enabled' => array(
                'StrokerCache\Strategy\RouteName' => array(
                    'routes' => array(
                        'foo/bar' => array(
                            'http_methods' => array('GET'),
                            'params' => array('id' => 60)
                        )
                    ),
                ),
            ),
        ),
    ),
);
```

## Change storage adapter

Storage adapter can be changed by configuration. Configuration structure is the same a StorageFactory consumes. See the [ZF2 reference guide](http://framework.zend.com/manual/2.0/en/modules/zend.cache.storage.adapter.html).
By default filesystem storage is used.

Example using APC:
```php
<?php
return array(
    'strokercache' => array(
        'storage_adapter' => array(
            'name' => 'Zend\Cache\Storage\Adapter\Apc',
        ),
    ),
);

```

## Clearing the cache

You can invalidate cache items using the provided console route. 
Alternatively you could pull `strokercache_service` from the servicelocator and call `clearByTags` directly from you application (i.e. from an event listener).

Run the following command from your project root:
`php public/index.php strokercache clear <tags>`

Multiple tags can be seperated by a `,`.
Every page which is cached by StrokerCache is identified using the following tags:
- `route_<routename>`: Contains the matched routename of the page
- `controller_<controllername>`: Contains the controllername
- `param_<paramname>_<paramvalue>`: One tag for every route param

To clear every page renderered by the `someAction` in `MyNamespace\MyController` do the following:
`php public/index.php strokercache clear controller_MyNamespace\MyController,param_action:some`

To clear the route with alias `player` but only for the player with id 60. 
`php public/index.php strokercache clear route_player,param_id_60`

## Custom id generators

You can create your own id generator by implementing the StrokerCache\IdGenerator\IdGeneratorInterface.
Now register your generator to the PluginManager:

```php
<?php
return array(
    'strokercache' => array(
        'id_generators' => array(
            'plugin_manager' => array(
                'invokables' => array(
                    'myGenerator' => 'MyNamespace\MyGenerator'
                ),
            ),
        ),
        'id_generator' => 'myGenerator'
    ),
);
```

## Custom strategies

You can create your own strategies by implementing the StrokerCache\Strategy\StrategyInterface. Now register your strategy to the pluginManager:

```php
<?php
return array(
    'strokercache' => array(
        'strategies' => array(
            'plugin_manager' => array(
                'invokables' => array(
                    'MyNamespace\MyCustomStrategy'
                ),
            ),
        ),
    ),
);
```

Next you need to enable the strategy

```php
<?php
return array(
    'strokercache' => array(
        'strategies' => array(
            'enabled' => array(
                'MyNamespace\MyCustomStrategy'
            ),
        ),
    ),
);
```

## Events

The cache service triggers several events you can utilize to add some custom logic whenever saving/loading the cache happens.
The events are listed as constants in the [CacheEvent](https://github.com/bramstroker/zf2-fullpage-cache/blob/master/src/StrokerCache/Event/CacheEvent.php) class:

- `EVENT_LOAD`: triggered when the requested page is found in the cache and ready to be served to the client
- `EVENT_SAVE`: triggered when your page is stored in the cache storage
- `EVENT_SHOULDCACHE`: this event is used to determine if a page should be stored into the cache. You can listen to this event if you don't want the page to be cached. All the strategies are attached to this event as well.

### Examples

Log to file whenever a page is written to the cache storage

```php
public function onBootstrap(MvcEvent $e)
{
    $serviceManager = $e->getApplication()->getServiceManager();
    $logger = new \Zend\Log\Logger();
    $logger->addWriter(new \Zend\Log\Writer\Stream('/log/strokercache.log'));
    $cacheService = $serviceManager->get('strokercache_service');
    $cacheService->getEventManager()->attach(CacheEvent::EVENT_SAVE, function (CacheEvent $e) use ($logger) {
        $logger->debug('Saving page to cache with ID: ' . $e->getCacheKey());
    });
}
```

Say we want to disable caching for all requests on port 8080, we can simply listen to the `SHOULDCACHE` event and return `false`.
Keep in mind you want to prevent other listeners from executing using `stopPropagation()`. If you don't do this other listeners will be executed and whenever one of them returns `true` the page will be cached.
Also you need to attach the listener at a higher priority (`1000` in this example) than the buildin strategies (they are registered at priority `100`).

```php
public function onBootstrap(MvcEvent $e)
{
    $serviceManager = $e->getApplication()->getServiceManager();
    $cacheService = $serviceManager->get('strokercache_service');
    $cacheService->getEventManager()->attach(CacheEvent::EVENT_SHOULDCACHE, function (CacheEvent $e) {
        if ($e->getMvcEvent()->getRequest()->getUri()->getPort() == 8080) {
            $e->stopPropagation(true);
            return false;
        }
        return true;
    }, 1000);
}
```

If you want to avoide caching because, for instance, the user is authenticated, do the same as above, but listen on `LOAD` instead of `SHOULDCACHE`:


```php
public function onBootstrap(MvcEvent $e)
{
    $serviceManager = $e->getApplication()->getServiceManager();
    $cacheService = $serviceManager->get('strokercache_service');
    $cacheService->getEventManager()->attach(CacheEvent::EVENT_LOAD, function (CacheEvent $e) {
        $loggedIn = /* your logic here */;
        if ($loggedIn) {
            $e->stopPropagation(true);
            return false;
        }
    }, 1000);
}
```

## Store directly to HTML files for max performance

TODO ...
