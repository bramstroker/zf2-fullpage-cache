<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use StrokerCache\Exception\RuntimeException;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheStrategyAbstractFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return (bool) strstr($name, 'strokercachestrategy');
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var $options \StrokerCache\Options\ModuleOptions */
        $options = $serviceLocator->getServiceLocator()->get('StrokerCache\Options\ModuleOptions');

        $fqcn = 'StrokerCache\\Strategy\\' . ucfirst(substr($name, 20));

        if (!class_exists($fqcn)) {
            throw new RuntimeException($fqcn . ' Not found');
        }

        $strategyOptions = array();
        $strategies = $options->getStrategies();
        if (isset($strategies['enabled'][$fqcn])) {
            $strategyOptions = $strategies['enabled'][$fqcn];
        }

        $strategy = new $fqcn($strategyOptions);

        return $strategy;
    }
}
