<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\IdGenerator;

use StrokerCache\Exception;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

class IdGeneratorPluginManager extends AbstractPluginManager
{
    /**
     * @var string
     */
    protected $instanceOf = IdGeneratorInterface::class;

    /**
     * @var array
     */
    protected $aliases = [
        'requesturi' => RequestUriGenerator::class,
        'fulluri'   => FullUriGenerator::class
    ];

    /**
     * Builtin generators
     *
     * @var array
     */
    protected $factories = [
        RequestUriGenerator::class => InvokableFactory::class,
        FullUriGenerator::class => InvokableFactory::class
    ];

    /**
     * {@inheritdoc}
     */
    public function validate($instance)
    {
        if ($instance instanceof $this->instanceOf) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\IdGeneratorInterface',
            (is_object($instance) ? get_class($instance) : gettype($instance)),
            __NAMESPACE__
        ));
    }

    /**
     * @deprecated to support ServiceManager v2
     */
    public function validatePlugin($instance)
    {
        $this->validate($instance);
    }
}
