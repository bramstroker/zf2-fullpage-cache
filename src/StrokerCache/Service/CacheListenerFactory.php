<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use StrokerCache\Listener\CacheListener;

class CacheListenerFactory implements \Zend\ServiceManager\FactoryInterface
{

    /**
     * Create service
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cacheService = $serviceLocator->get('StrokerCache\Service\CacheService');

        return new CacheListener($cacheService);
    }
}
