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

namespace StrokerCache\Strategy;

use Zend\Mvc\MvcEvent;
use Zend\Stdlib\AbstractOptions;

class Url extends AbstractOptions implements StrategyInterface
{
    /**
     * @var array
     */
    private $regexpes;

    /**
     * True if the request should be cached
     *
     * @param MvcEvent $event
     * @return boolean
     */
    public function shouldCache(MvcEvent $event)
    {
        $uri = $event->getRequest()->getUri();
        foreach($this->getRegexpes() as $regex) {
            if (preg_match($regex, $uri->getPath())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cache tags to use for this page
     *
     * @param \Zend\Mvc\MvcEvent $event
     * @return array
     */
    public function getTags(MvcEvent $event)
    {
        return array();
    }

    /**
     * @return array
     */
    public function getRegexpes()
    {
        return $this->regexpes;
    }

    /**
     * @param array $regexpes
     */
    public function setRegexpes($regexpes)
    {
        $this->regexpes = $regexpes;
    }
}
