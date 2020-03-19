<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @author Freek Gruntjes fgruntjes@emico.nl
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Storage\Adapter;

use RuntimeException;
use Laminas\Cache\Storage\Adapter\AdapterOptions;

class FileOptions extends AdapterOptions
{
    /**
     * @var string
     */
    protected $baseDirectory;

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @param  string           $baseDirectory
     * @throws RuntimeException
     */
    public function setBaseDirectory($baseDirectory)
    {
        if (!is_dir($baseDirectory)) {
            throw new RuntimeException('Cache directory: "' . $baseDirectory . '" is not a directory.');
        }

        if (!is_writable($baseDirectory)) {
            throw new RuntimeException('Cache directory: "' . $baseDirectory . '" is not writable.');
        }
        $this->baseDirectory = $baseDirectory;
    }
}
