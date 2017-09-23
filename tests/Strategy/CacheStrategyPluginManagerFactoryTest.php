<?php
/**
 * Created by PhpStorm.
 * User: bram
 * Date: 4-1-14
 * Time: 12:12
 */

namespace StrokerCacheTest\IdGenerator;

use StrokerCache\Factory\CacheStrategyPluginManagerFactory;
use StrokerCache\Strategy\CacheStrategyPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;

class CacheStrategyPluginManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsInstance()
    {
        $container = new ServiceManager();
        $container->setService(
            'Config',
            [
                'strokercache' => [
                    'strategies' => [
                        'plugin_manager' => [
                            'factories' => [
                                'foo' => InvokableFactory::class
                            ]
                        ],
                    ]
                ]
            ]
        );

        $factory = new CacheStrategyPluginManagerFactory();
        $pluginManager = $factory->__invoke($container, 'foo');

        $this->assertInstanceOf(CacheStrategyPluginManager::class, $pluginManager);
    }
}
