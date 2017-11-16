<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest;

use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;
use StrokerCache\Listener\CacheListener;
use StrokerCache\Module;
use StrokerCache\Options\ModuleOptions;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    public function testListenerIsNotAttachedWhenCachingIsDisabled()
    {
        $options = new ModuleOptions();
        $options->setEnabled(false);

        $serviceManager = $this->createServiceManager($options);

        $serviceManager
            ->shouldReceive('get')
            ->with(CacheListener::class)
            ->never();

        $event = new MvcEvent();
        $event->setApplication($this->createApplication($serviceManager));

        $module = new Module();

        $module->onBootstrap($event);
    }

    public function testListenerIsAttachedWhenCachingIsEnabled()
    {
        $cacheListenerMock = Mockery::mock(CacheListener::class);
        $cacheListenerMock
            ->shouldReceive('attach')
            ->with(Mockery::type(EventManagerInterface::class))
            ->getMock();

        $serviceManager = $this->createServiceManager();

        $serviceManager
            ->shouldReceive('get')
            ->with(CacheListener::class)
            ->andReturn($cacheListenerMock);

        $event = new MvcEvent();
        $event->setApplication($this->createApplication($serviceManager));

        $module = new Module();

        $module->onBootstrap($event);
    }

    /**
     * @param ModuleOptions|null $moduleOptions
     * @return MockInterface|ServiceManager
     */
    protected function createServiceManager(ModuleOptions $moduleOptions = null)
    {
        if ($moduleOptions === null) {
            $moduleOptions = new ModuleOptions();
        }

        $serviceManager = Mockery::mock(ServiceManager::class);

        $serviceManager
            ->shouldReceive('get')
            ->with(ModuleOptions::class)
            ->andReturn($moduleOptions);

        return $serviceManager;
    }

    /**
     * @param ServiceManager $serviceManager
     * @return MockInterface|Application
     */
    protected function createApplication(ServiceManager $serviceManager)
    {
        $applicationMock = Mockery::mock(Application::class);

        $applicationMock
            ->shouldReceive('getServiceManager')
            ->andReturn($serviceManager);

        $applicationMock
            ->shouldReceive('getEventManager')
            ->andReturn(Mockery::mock(EventManagerInterface::class));

        return $applicationMock;
    }
}
