<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\IdGenerator;

use StrokerCache\Exception\RuntimeException;

class ExtendedGenerator implements IdGeneratorInterface
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

        $port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (':'.$_SERVER['SERVER_PORT']);
        $scheme = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0 ? 'https' : 'http';

        return $scheme . '://'.$_SERVER['HTTP_HOST']. $port . $_SERVER['REQUEST_URI'];
    }
}
