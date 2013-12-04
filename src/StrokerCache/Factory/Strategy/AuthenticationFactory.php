<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Factory\Strategy;

use StrokerCache\Strategy\Authentication;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $locator = $serviceLocator->getServiceLocator();

        /** @var $options \StrokerCache\Options\ModuleOptions */
        $options = $locator->getServiceLocator()->get('StrokerCache\Options\ModuleOptions');

        $authenticationStrategy = new Authentication($locator->get('Zend\Authentication\AuthenticationService'));
        $authenticationStrategy->setFromArray($options->getStrategyOptions($authenticationStrategy));
    }
}
