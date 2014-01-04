<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\IdGenerator;

use StrokerCache\Exception;
use StrokerCache\IdGenerator\IdGeneratorInterface;
use Zend\ServiceManager\AbstractPluginManager;

class IdGeneratorPluginManager extends AbstractPluginManager
{
    /**
     * Builtin generators
     *
     * @var array
     */
    protected $invokableClasses = array(
        'requesturi' => 'StrokerCache\IdGenerator\RequestUriGenerator',
        'extended'   => 'StrokerCache\IdGenerator\ExtendedGenerator'
    );

    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof IdGeneratorInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidStrategyException(sprintf(
            'Plugin of type %s is invalid; must implement %s\IdGeneratorInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
