<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;

class UriPath extends AbstractStrategy
{
    /**
     * @var array
     */
    protected $regexpes = [];

    /**
     * {@inheritDoc}
     */
    public function shouldCache(MvcEvent $event)
    {
        $request = $event->getRequest();
        if (!$request instanceof HttpRequest) {
            return false;
        }

        $uri = $request->getUri();
        foreach ($this->getRegexpes() as $regex) {
            if (preg_match($regex, $uri->getPath())) {
                return true;
            }
        }

        return false;
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
    public function setRegexpes(array $regexpes)
    {
        $this->regexpes = $regexpes;
    }
}
