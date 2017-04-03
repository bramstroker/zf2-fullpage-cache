<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Event;

use StrokerCache\Event\CacheEvent;
use Zend\Mvc\MvcEvent;

class CacheEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetMvcEvent()
    {
        $cacheEvent = new CacheEvent();
        $mvcEvent = new MvcEvent();
        $cacheEvent->setMvcEvent($mvcEvent);
        $this->assertEquals($mvcEvent, $cacheEvent->getMvcEvent());
    }
}
