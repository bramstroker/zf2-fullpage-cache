<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory;

use Interop\Container\ContainerInterface;
use StrokerCache\Listener\CacheListener;
use StrokerCache\Options\ModuleOptions;
use StrokerCache\Service\CacheService;
use Zend\ServiceManager\Factory\FactoryInterface;

class CacheListenerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new CacheListener(
            $container->get(CacheService::class),
            $container->get(ModuleOptions::class)
        );
    }
}
