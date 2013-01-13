<?php
/*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
* This software consists of voluntary contributions made by many individuals
* and is licensed under the MIT license.
*/

namespace StrokerCache;

use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements
    ConfigProviderInterface,
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    ServiceProviderInterface,
    ControllerProviderInterface
{

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Return an array for passing to Zend\Loader\AutoloaderFactory.
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Listen to the bootstrap event
     *
     * @param  EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var $application \Zend\Mvc\Application */
        $application = $e->getParam('application');
        $listener = $application->getServiceManager()->get('StrokerCache\Listener\CacheListener');
        $application->getEventManager()->attach($listener);
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'StrokerCache\Listener\CacheListener' => 'StrokerCache\Service\CacheListenerFactory',
                'strokerCache\Service\CacheService' => 'StrokerCache\Service\CacheServiceFactory',
                'StrokerCache\Options\ModuleOptions' => 'StrokerCache\Service\ModuleOptionsFactory',
                'StrokerCache\Strategy\PluginManager' => 'StrokerCache\Strategy\PluginManagerFactory',
                'strokercache_storage' => 'StrokerCache\Service\CacheStorageFactory'
            ),
            'aliases' => array(
                'strokercache_service' => 'StrokerCache\Service\CacheService'
            )
        );
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to seed
     * such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'StrokerCache\Controller\Cache' => 'StrokerCache\Service\CacheControllerFactory'
            )
        );
    }
}
