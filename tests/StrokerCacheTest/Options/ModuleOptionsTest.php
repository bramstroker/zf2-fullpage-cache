<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Options;

use StrokerCache\Options\ModuleOptions;

class ModuleOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var ModuleOptions */
    protected $options;

    public function setUp()
    {
        $this->options = new ModuleOptions();
    }

    public function testGetSetStrategies()
    {
        $strategies = array('foo', 'bar');
        $this->options->setStrategies($strategies);
        $this->assertEquals($strategies, $this->options->getStrategies());
    }

    public function testGetSetStorageAdapter()
    {
        $storageAdapter = array(
            'name' => 'foo',
            'options' => array(
                'foo' => 'bar'
            )
        );
        $this->options->setStorageAdapter($storageAdapter);
        $this->assertEquals($storageAdapter, $this->options->getStorageAdapter());
    }
}
