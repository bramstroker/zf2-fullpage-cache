<?php
/**
 * @author Aeneas Rekkas
 * @copyright (c) Aeneas Rekkas 2014
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use Interop\Container\ContainerInterface;
use StrokerCache\IdGenerator\IdGeneratorPluginManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class IdGeneratorPluginManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config      = $container->get('Config');
        $pluginManager = new IdGeneratorPluginManager($container, $config['strokercache']['id_generators']['plugin_manager']);
        return $pluginManager;
    }
}
