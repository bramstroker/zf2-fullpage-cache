<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\IdGenerator;

use StrokerCache\Exception\RuntimeException;

class RequestUriGenerator implements IdGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function generate()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            throw new RuntimeException("Can't auto-detect current page identity");
        }

        return md5($_SERVER['REQUEST_URI']);
    }
}