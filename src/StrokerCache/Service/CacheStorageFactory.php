<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheStorageFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $options \StrokerCache\Options\ModuleOptions */
        $options = $serviceLocator->get('StrokerCache\Options\ModuleOptions');
        $adapterOptions = array('adapter' => $options->getStorageAdapter());

        return StorageFactory::factory($adapterOptions);
    }
}
