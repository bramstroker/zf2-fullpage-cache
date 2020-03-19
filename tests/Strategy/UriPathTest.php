<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\UriPath;
use Laminas\Mvc\MvcEvent;
use Laminas\Uri\Http;
use Laminas\Uri\Uri;
use Laminas\Http\Request as HttpRequest;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UriPath
     */
    private $strategy;

    public function setUp()
    {
        $this->strategy = new UriPath();
    }

    /**
     * @return array
     */
    public static function shouldCacheProvider()
    {
        return array(
            'match-full' => array(array('/^foobar$/'), 'foobar', true),
            'nomatch-full' => array(array('/^foobar$/'), 'baz', false),
            'match-part' => array(array('/foobar/'), 'aafoobarbb', true)
        );
    }

    /**
     * @param array  $regexpes
     * @param string $uriPath
     * @param bool   $expectedResult
     * @dataProvider shouldCacheProvider
     */
    public function testShouldCache($regexpes, $uriPath, $expectedResult)
    {
        $this->strategy->setRegexpes($regexpes);
        $mvcEvent = new MvcEvent();
        $request = new HttpRequest();
        $uri = new Http();
        $uri->setPath($uriPath);
        $request->setUri($uri);
        $mvcEvent->setRequest($request);
        $this->assertEquals($expectedResult, $this->strategy->shouldCache($mvcEvent));
    }

    public function testStrategyExtendsAbstractStrategy()
    {
        $this->assertInstanceOf('StrokerCache\Strategy\AbstractStrategy', $this->strategy);
    }
}
