# StrokerCache

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

- By routename and params
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

## Clearing the cache

Todo

## Custom strategies

You can create your own strategies by implementing the StrokerCache\Strategy\StrategyInterface. Now register your strategy to the pluginManager:

```php
<?php
return array(
    'strokercache' => array(
        'strategies' => array(
            'plugin_manager' => array(
                'invokables' => array(
                    'MyNamespace\MyCustomStrategy
                ),
            ),
        ),
    ),
);
```