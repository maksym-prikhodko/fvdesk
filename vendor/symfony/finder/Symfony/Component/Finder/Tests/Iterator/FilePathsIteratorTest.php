<?php
namespace Symfony\Component\Finder\Tests\Iterator;
use Symfony\Component\Finder\Iterator\FilePathsIterator;
class FilePathsIteratorTest extends RealIteratorTestCase
{
    public function testSubPath($baseDir, array $paths, array $subPaths, array $subPathnames)
    {
        $iterator = new FilePathsIterator($paths, $baseDir);
        foreach ($iterator as $index => $file) {
            $this->assertEquals($paths[$index], $file->getPathname());
            $this->assertEquals($subPaths[$index], $iterator->getSubPath());
            $this->assertEquals($subPathnames[$index], $iterator->getSubPathname());
        }
    }
    public function getSubPathData()
    {
        $tmpDir = sys_get_temp_dir().'/symfony_finder';
        return array(
            array(
                $tmpDir,
                array(
                    $tmpDir.DIRECTORY_SEPARATOR.'.git' => $tmpDir.DIRECTORY_SEPARATOR.'.git',
                    $tmpDir.DIRECTORY_SEPARATOR.'test.py' => $tmpDir.DIRECTORY_SEPARATOR.'test.py',
                    $tmpDir.DIRECTORY_SEPARATOR.'foo' => $tmpDir.DIRECTORY_SEPARATOR.'foo',
                    $tmpDir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar.tmp' => $tmpDir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar.tmp',
                    $tmpDir.DIRECTORY_SEPARATOR.'test.php' => $tmpDir.DIRECTORY_SEPARATOR.'test.php',
                    $tmpDir.DIRECTORY_SEPARATOR.'toto' => $tmpDir.DIRECTORY_SEPARATOR.'toto',
                ),
                array(
                    $tmpDir.DIRECTORY_SEPARATOR.'.git' => '',
                    $tmpDir.DIRECTORY_SEPARATOR.'test.py' => '',
                    $tmpDir.DIRECTORY_SEPARATOR.'foo' => '',
                    $tmpDir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar.tmp' => 'foo',
                    $tmpDir.DIRECTORY_SEPARATOR.'test.php' => '',
                    $tmpDir.DIRECTORY_SEPARATOR.'toto' => '',
                ),
                array(
                    $tmpDir.DIRECTORY_SEPARATOR.'.git' => '.git',
                    $tmpDir.DIRECTORY_SEPARATOR.'test.py' => 'test.py',
                    $tmpDir.DIRECTORY_SEPARATOR.'foo' => 'foo',
                    $tmpDir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar.tmp' => 'foo'.DIRECTORY_SEPARATOR.'bar.tmp',
                    $tmpDir.DIRECTORY_SEPARATOR.'test.php' => 'test.php',
                    $tmpDir.DIRECTORY_SEPARATOR.'toto' => 'toto',
                ),
            ),
        );
    }
}
