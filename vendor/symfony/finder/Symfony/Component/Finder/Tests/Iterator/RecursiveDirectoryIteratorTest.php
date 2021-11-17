<?php
namespace Symfony\Component\Finder\Tests\Iterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
class RecursiveDirectoryIteratorTest extends IteratorTestCase
{
    public function testRewind($path, $seekable, $contains, $message = null)
    {
        try {
            $i = new RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        } catch (\UnexpectedValueException $e) {
            $this->markTestSkipped(sprintf('Unsupported stream "%s".', $path));
        }
        $i->rewind();
        $this->assertTrue(true, $message);
    }
    public function testSeek($path, $seekable, $contains, $message = null)
    {
        try {
            $i = new RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        } catch (\UnexpectedValueException $e) {
            $this->markTestSkipped(sprintf('Unsupported stream "%s".', $path));
        }
        $actual = array();
        $i->seek(0);
        $actual[] = $i->getPathname();
        $i->seek(1);
        $actual[] = $i->getPathname();
        $i->seek(2);
        $actual[] = $i->getPathname();
        $this->assertEquals($contains, $actual);
    }
    public function getPaths()
    {
        $data = array();
        $contains = array(
            'ftp:
            'ftp:
            'ftp:
        );
        $data[] = array('ftp:
        return $data;
    }
}
