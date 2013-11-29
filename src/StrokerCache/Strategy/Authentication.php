<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Strategy;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\AbstractOptions;

class Authentication extends AbstractOptions implements StrategyInterface
{
    /**
     * @var bool
     */
    protected $cacheIfIdentityExists = false;

    /**
     * @var string
     */
    protected $identity;

    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldCache(MvcEvent $event)
    {
        //true & false = false
        //true & true  = true
        //false & false = true
        //false & true  = false
        $shouldCache = ($this->getCacheIfIdentityExists() ^ $this->authenticationService->hasIdentity()) == false;
        if (!empty($this->identity) && $this->identity !== $this->authenticationService->getIdentity()) {
            $shouldCache = !$shouldCache;
        }
        return $shouldCache;
    }

    /**
     * @return boolean
     */
    public function getCacheIfIdentityExists()
    {
        return $this->cacheIfIdentityExists;
    }

    /**
     * @param boolean $cacheIfIdentityExists
     */
    public function setCacheIfIdentityExists($cacheIfIdentityExists)
    {
        $this->cacheIfIdentityExists = $cacheIfIdentityExists;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param string $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }
}
