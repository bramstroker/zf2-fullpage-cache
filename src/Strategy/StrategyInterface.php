<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use Zend\Mvc\MvcEvent;

interface StrategyInterface
{
    /**
     * True if the request should be cached
     *
     * @param  MvcEvent $event
     * @return boolean
     */
    public function shouldCache(MvcEvent $event);
}
