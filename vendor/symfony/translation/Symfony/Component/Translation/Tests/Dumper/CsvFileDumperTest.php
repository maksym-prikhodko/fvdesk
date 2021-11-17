<?php
namespace Symfony\Component\Translation\Tests\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Dumper\CsvFileDumper;
class CsvFileDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->add(array('foo' => 'bar', 'bar' => 'foo
foo', 'foo;foo' => 'bar'));
        $tempDir = sys_get_temp_dir();
        $dumper = new CsvFileDumper();
        $dumper->dump($catalogue, array('path' => $tempDir));
        $this->assertEquals(file_get_contents(__DIR__.'/../fixtures/valid.csv'), file_get_contents($tempDir.'/messages.en.csv'));
        unlink($tempDir.'/messages.en.csv');
    }
}
