<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Strategy;

use Mockery as M;
use StrokerCache\Strategy\Authentication;
use Zend\Mvc\MvcEvent;

class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Authentication
     */
    protected $strategy;

    /**
     * @var MockInterface|AuthenticationService
     */
    protected $authenticationServiceMock;

    public function setUp()
    {
        $this->authenticationServiceMock = M::mock('Zend\Authentication\AuthenticationService');
        $this->strategy = new Authentication($this->authenticationServiceMock);
    }

    public function testCacheIsDisabledWhenUserIdentityIsFound()
    {
        $this->authenticationServiceMock->shouldReceive('hasIdentity')->andReturn(true);

        $this->strategy->setCacheIfIdentityExists(false);
        $this->assertFalse($this->strategy->shouldCache(new MvcEvent()));
    }

    public function testCacheIsDisabledOnlyForSpecificIdentity()
    {
        $this->authenticationServiceMock->shouldReceive('hasIdentity')->andReturn(true);
        $this->authenticationServiceMock->shouldReceive('getIdentity')->andReturn('foo');

        $this->strategy->setCacheIfIdentityExists(false);
        $this->strategy->setIdentity('foo');
        $this->assertFalse($this->strategy->shouldCache(new MvcEvent()));
        $this->strategy->setIdentity('bar');
        $this->assertTrue($this->strategy->shouldCache(new MvcEvent()));
    }

    public function testCacheIsEnabledWhenNoUserIdentityIsFound()
    {
        $this->authenticationServiceMock->shouldReceive('hasIdentity')->andReturn(false);

        $this->strategy->setCacheIfIdentityExists(false);
        $this->assertTrue($this->strategy->shouldCache(new MvcEvent()));
    }

    public function testCacheIsEnabledWhenUserIdentityIsFound()
    {
        $this->authenticationServiceMock->shouldReceive('hasIdentity')->andReturn(true);

        $this->strategy->setCacheIfIdentityExists(true);
        $this->assertTrue($this->strategy->shouldCache(new MvcEvent()));
    }

    public function testCacheIsEnabledOnlyForSpecificIdentity()
    {
        $this->authenticationServiceMock->shouldReceive('hasIdentity')->andReturn(true);
        $this->authenticationServiceMock->shouldReceive('getIdentity')->andReturn('foo');

        $this->strategy->setCacheIfIdentityExists(true);
        $this->strategy->setIdentity('foo');
        $this->assertTrue($this->strategy->shouldCache(new MvcEvent()));

        $this->strategy->setIdentity('bar');
        $this->assertFalse($this->strategy->shouldCache(new MvcEvent()));
    }
}
