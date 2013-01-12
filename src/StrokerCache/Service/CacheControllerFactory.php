<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use StrokerCache\Controller\CacheController;
use Zend\ServiceManager\FactoryInterface;

class CacheControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $locator = $serviceLocator->getServiceLocator();

        return new CacheController(
            $locator->get('StrokerCache\Service\CacheService'),
            $locator->get('Console')
        );
    }
}
