<?php
/**
 * @author Bram Gerritsen bgerritsen@gmail.com
 * @author Freek Gruntjes fgruntjes@emico.nl
 * @copyright (c) Bram Gerritsen 2013
 * @license http://opensource.org/licenses/mit-license.php
 */

namespace StrokerCacheTest\Storage\Adapter;

use org\bovigo\vfs\vfsStream;
use StrokerCache\Storage\Adapter\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
    public function domainToFileConversionProvider()
    {
        return array(
            'with_slash' => array('http://www.domain.com/', 'http/www.domain.com/index.html'),
            'without_slash' => array('http://www.domain.com', 'http/www.domain.com/index.html'),
            'path' => array('http://www.domain.com/path', 'http/www.domain.com/path.html'),
            'path_html' => array('http://www.domain.com/path_html.html', 'http/www.domain.com/path_html.html.html'),
            'query'  => array('http://www.domain.com?test=15', 'http/www.domain.com/index-test=15.html'),
            'long_query'  => array('http://www.domain.com?test=15&test2=ja', 'http/www.domain.com/index-test=15&test2=ja.html'),
            'long_path' => array('http://www.domain.com/foo/bar/bos', 'http/www.domain.com/foo/bar/bos.html'),
        );
    }

    /**
     * Test storing different urls
     *
     * @dataProvider domainToFileConversionProvider
     */
    public function testDomainToFileConversion($domain, $file)
    {
        $dir = vfsStream::setup('tmp-dir');
        $adapter = new File(array(
            'base_directory' => $dir->url(),
        ));

        $data = md5(rand());
        $adapter->setItem($domain, $data);
        $this->assertTrue(file_exists($dir->url() . DIRECTORY_SEPARATOR . $file));
        $this->assertEquals($data, $adapter->getItem($domain, $result));
        $this->assertTrue($result);
        $this->assertEquals(null, $adapter->getItem($domain . 'junk', $result));
        $this->assertFalse($result);
        $adapter->removeItem($domain);
        $this->assertFalse(file_exists($dir->url() . DIRECTORY_SEPARATOR . $file));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cache directory: "vfs://tmp-dir" is not writable.
     */
    public function testNotWritableBaseDir()
    {
        $dir = vfsStream::setup('tmp-dir');
        $dir->chmod(0000);
        new File(array(
            'base_directory' => $dir->url(),
        ));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cache directory: "vfs://tmp-dir//tmp" is not a directory.
     */
    public function testBaseDirFile()
    {
        $dir = vfsStream::setup('tmp-dir');
        touch($dir->url() . DIRECTORY_SEPARATOR . '/tmp');
        new File(array(
            'base_directory' => $dir->url() . DIRECTORY_SEPARATOR . '/tmp',
        ));
    }

    /**
     * Test file check when permissions change in between
     */
    public function testFileChangePermissions()
    {
        $dir = vfsStream::setup('tmp-dir');
        $adapter = new File(array(
            'base_directory' => $dir->url(),
        ));

        $adapter->setItem('http://www.domain.com/', 'junk');
        $file = $dir->url() . DIRECTORY_SEPARATOR . 'http/www.domain.com/index.html';
        $this->assertTrue(file_exists($file));
        $dir->getChild('http/www.domain.com/index.html')->chmod(0000);
        $this->assertFalse($adapter->removeItem('http://www.domain.com/'));
    }
}