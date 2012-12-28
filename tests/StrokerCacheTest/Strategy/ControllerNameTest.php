<?php
/*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
* This software consists of voluntary contributions made by many individuals
* and is licensed under the MIT license.
*/

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\ControllerName;

class ControllerNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerName
     */
    private $strategy;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->strategy = new ControllerName();
    }

    /**
     * @return array
     */
    public static function shouldCacheProvider()
    {
        return array(
            array(array('Namespace\TestController'), 'Namespace\TestController', true),
            array(array(), 'Namespace\TestController', false),
            array(array('Namespace\TestController2'), 'Namespace\TestController', false),
        );
    }

    /**
     * @param array $controllers
     * @param string $requestedController
     * @param boolean $expectedResult
     * @dataProvider shouldCacheProvider
     */
    public function testShouldCache($controllers, $requestedController, $expectedResult)
    {
        $this->strategy->setControllers($controllers);
        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $mvcEvent->setControllerClass($requestedController);
        $this->assertEquals($expectedResult, $this->strategy->shouldCache($mvcEvent));
    }
}
