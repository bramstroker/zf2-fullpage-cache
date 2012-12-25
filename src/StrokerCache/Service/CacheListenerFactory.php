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

namespace StrokerCache\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

class CacheListenerFactory implements \Zend\ServiceManager\FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cacheStorage = $serviceLocator->get('strokercache_storage');

        /** @var $options \StrokerCache\Options\ModuleOptions */
        $options = $serviceLocator->get('StrokerCache\Options\ModuleOptions');

        $cacheListener = new \StrokerCache\Listener\CacheListener(
            $cacheStorage,
            $options
        );

        // Register enabled strategies on the cacheListener
        $strategies = $options->getStrategies();
        if (isset($strategies['enabled'])) {
            /** @var $strategyPluginManager \StrokerCache\Strategy\PluginManager */
            $strategyPluginManager = $serviceLocator->get('StrokerCache\Strategy\PluginManager');

            foreach ($strategies['enabled'] as $alias => $options)
            {
                if (is_numeric($alias)) {
                    $alias = $options;
                }
                $strategy = $strategyPluginManager->get($alias);
                $cacheListener->addStrategy($strategy);
            }
        }

        return $cacheListener;
    }
}
