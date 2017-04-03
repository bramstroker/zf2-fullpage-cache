<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\IdGenerator;

interface IdGeneratorInterface
{
    /**
     * Compose a ID to use for saving to the cache
     *
     * @return string
     */
    public function generate();
}
