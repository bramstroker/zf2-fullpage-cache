# StrokerCache

[![Build Status](https://travis-ci.org/bramstroker/zf2-fullpage-cache.png?branch=master)](https://travis-ci.org/bramstroker/zf2-fullpage-cache)

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

## Change storage adapter

Storage adapter can be changed by configuration. Configuration structure is the same a StorageFactory consumes. See the [ZF2 reference guide](http://framework.zend.com/manual/2.0/en/modules/zend.cache.storage.adapter.html).
By default filesystem storage is used.

Example using APC:
```php
<?php
return array(
    'strokercache' => array(
        'storage_adapter' => array(
            'apc'
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
