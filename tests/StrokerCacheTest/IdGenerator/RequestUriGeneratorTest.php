<?php
/**
 * Created by PhpStorm.
 * User: bram
 * Date: 4-1-14
 * Time: 12:12
 */

namespace StrokerCacheTest\IdGenerator;


use StrokerCache\IdGenerator\RequestUriGenerator;

class RequestUriGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateCorrectId()
    {
        $generator = new RequestUriGenerator();
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $this->assertEquals(md5('/foo/bar'), $generator->generate());
    }

    /**
     * @expectedException \StrokerCache\Exception\RuntimeException
     */
    public function testGenerateThrowsExceptionWhenRequestUriIsNotAvailable()
    {
        $generator = new RequestUriGenerator();
        unset($_SERVER['REQUEST_URI']);
        $generator->generate();
    }
} 