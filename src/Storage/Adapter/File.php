<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @author Freek Gruntjes fgruntjes@emico.nl
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCache\Storage\Adapter;

use SplFileInfo;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;

class File extends AbstractAdapter
{
    /**
     * {@inheritDoc}
     */
    public function setOptions($options)
    {
        if (!$options instanceof FileOptions && is_array($options)) {
            $options = new FileOptions($options);
        }

        return parent::setOptions($options);
    }

    /**
     * @param  string      $url
     * @return SplFileInfo
     */
    protected function getFileForUrl($url)
    {
        $urlParts = parse_url($url);

        /** @var FileOptions $options */
        $options = $this->getOptions();

        $path = $options->getBaseDirectory() . DIRECTORY_SEPARATOR .
            $urlParts['scheme'] . DIRECTORY_SEPARATOR .
            $urlParts['host'] . DIRECTORY_SEPARATOR;

        if (isset($urlParts['path'])) {
            $path .= ltrim($urlParts['path'], '/');
        }

        if (substr($path, -1) == '/') {
            $path .= 'index';
        }

        if (isset($urlParts['query'])) {
            $path .= '-' . $urlParts['query'];
        }

        return new SplFileInfo($path . '.html');
    }

    /**
     * {@inheritDoc}
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $file = $this->getFileForUrl($normalizedKey);
        if (!$file->isReadable()) {
            $success = false;

            return null;
        }
        $success = true;

        return file_get_contents($file->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $file = $this->getFileForUrl($normalizedKey);
        $dirname = dirname($file->getPathname());
        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }
        file_put_contents($file, $value);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $file = $this->getFileForUrl($normalizedKey);
        if (!$file->isWritable()) {
            return false;
        }
        unlink($file->getPathname());

        return true;
    }
}
