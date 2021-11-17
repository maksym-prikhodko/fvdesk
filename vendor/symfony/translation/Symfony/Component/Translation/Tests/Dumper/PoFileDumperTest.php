<?php
namespace Symfony\Component\Translation\Tests\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Dumper\PoFileDumper;
class PoFileDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->add(array('foo' => 'bar'));
        $tempDir = sys_get_temp_dir();
        $dumper = new PoFileDumper();
        $dumper->dump($catalogue, array('path' => $tempDir));
        $this->assertEquals(file_get_contents(__DIR__.'/../fixtures/resources.po'), file_get_contents($tempDir.'/messages.en.po'));
        unlink($tempDir.'/messages.en.po');
    }
}
