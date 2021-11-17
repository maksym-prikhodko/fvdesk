<?php
namespace Symfony\Component\Finder\Tests\Iterator;
use Symfony\Component\Finder\Iterator\CustomFilterIterator;
class CustomFilterIteratorTest extends IteratorTestCase
{
    public function testWithInvalidFilter()
    {
        new CustomFilterIterator(new Iterator(), array('foo'));
    }
    public function testAccept($filters, $expected)
    {
        $inner = new Iterator(array('test.php', 'test.py', 'foo.php'));
        $iterator = new CustomFilterIterator($inner, $filters);
        $this->assertIterator($expected, $iterator);
    }
    public function getAcceptData()
    {
        return array(
            array(array(function (\SplFileInfo $fileinfo) { return false; }), array()),
            array(array(function (\SplFileInfo $fileinfo) { return preg_match('/^test/', $fileinfo) > 0; }), array('test.php', 'test.py')),
            array(array('is_dir'), array()),
        );
    }
}
