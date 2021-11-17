<?php
namespace Symfony\Component\Process\Tests;
use Symfony\Component\Process\PhpExecutableFinder;
class PhpExecutableFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFindWithPhpPath()
    {
        if (defined('PHP_BINARY')) {
            $this->markTestSkipped('The PHP binary is easily available as of PHP 5.4');
        }
        $f = new PhpExecutableFinder();
        $current = $f->find();
        putenv('PHP_PATH=/not/executable/php');
        $this->assertFalse($f->find(), '::find() returns false for not executable PHP');
        $this->assertFalse($f->find(false), '::find() returns false for not executable PHP');
        putenv('PHP_PATH='.$current);
        $this->assertEquals($f->find(), $current, '::find() returns the executable PHP');
        $this->assertEquals($f->find(false), $current, '::find() returns the executable PHP');
    }
    public function testFindWithHHVM()
    {
        if (!defined('HHVM_VERSION')) {
            $this->markTestSkipped('Should be executed in HHVM context.');
        }
        $f = new PhpExecutableFinder();
        $current = $f->find();
        $this->assertEquals($f->find(), $current.' --php', '::find() returns the executable PHP');
        $this->assertEquals($f->find(false), $current, '::find() returns the executable PHP');
    }
    public function testFindArguments()
    {
        $f = new PhpExecutableFinder();
        if (defined('HHVM_VERSION')) {
            $this->assertEquals($f->findArguments(), array('--php'), '::findArguments() returns HHVM arguments');
        } else {
            $this->assertEquals($f->findArguments(), array(), '::findArguments() returns no arguments');
        }
    }
    public function testFindWithSuffix()
    {
        if (defined('PHP_BINARY')) {
            $this->markTestSkipped('The PHP binary is easily available as of PHP 5.4');
        }
        putenv('PHP_PATH=');
        putenv('PHP_PEAR_PHP_BIN=');
        $f = new PhpExecutableFinder();
        $current = $f->find();
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertTrue(is_executable($current));
            $this->assertTrue((bool) preg_match('/'.addSlashes(DIRECTORY_SEPARATOR).'php\.(exe|bat|cmd|com)$/i', $current), '::find() returns the executable PHP with suffixes');
        }
    }
}
