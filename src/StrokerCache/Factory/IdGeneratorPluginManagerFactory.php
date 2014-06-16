<?php
/**
 * @author Aeneas Rekkas
 * @copyright (c) Aeneas Rekkas 2014
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use StrokerCache\IdGenerator\IdGeneratorPluginManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IdGeneratorPluginManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config      = $serviceLocator->get('Config');
        $configClass = new Config($config['strokercache']['id_generators']['plugin_manager']);

        return new IdGeneratorPluginManager($configClass);
    }
}
