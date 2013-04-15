<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheStrategyAbstractFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return (bool) strstr($requestedName, 'StrokerCache\\Strategy');
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var $options \StrokerCache\Options\ModuleOptions */
        $options = $serviceLocator->getServiceLocator()->get('StrokerCache\Options\ModuleOptions');

        if (!class_exists($requestedName)) {
            throw new \RuntimeException($requestedName . ' Not found');
        }

        $strategyOptions = array();
        $strategies = $options->getStrategies();
        if (isset($strategies['enabled'][$requestedName])) {
            $strategyOptions = $strategies['enabled'][$requestedName];
        }

        $strategy = new $requestedName($strategyOptions);

        return $strategy;
    }
}
