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

use Zend\ServiceManager\AbstractPluginManager;
use StrokerCache\Exception;

class PluginManager extends AbstractPluginManager
{
    /**
     * Validate the plugin
     *
     * Checks that the helper loaded is an instance of Helper\HelperInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidStrategyException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof StrategyInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidStrategyException(sprintf(
            'Plugin of type %s is invalid; must implement %s\StrategyInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
