<?php
/**
 * Created by PhpStorm.
 * User: bram
 * Date: 4-1-14
 * Time: 12:12
 */

namespace StrokerCacheTest\IdGenerator;

use StrokerCache\Factory\IdGeneratorPluginManagerFactory;
use StrokerCache\IdGenerator\IdGeneratorPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;

class IdGeneratorPluginManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsInstance()
    {
        $container = new ServiceManager();
        $container->setService(
            'Config',
            [
                'strokercache' => [
                    'id_generators' => [
                        'plugin_manager' => [
                            'factories' => [
                                'foo' => InvokableFactory::class
                            ]
                        ],
                    ]
                ]
            ]
        );

        $factory = new IdGeneratorPluginManagerFactory();
        $pluginManager = $factory->__invoke($container, 'foo');

        $this->assertInstanceOf(IdGeneratorPluginManager::class, $pluginManager);
    }
}
