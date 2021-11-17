<?php
namespace Symfony\Component\Translation\Tests\Dumper;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Dumper\QtFileDumper;
class QtFileDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->add(array('foo' => 'bar'), 'resources');
        $tempDir = sys_get_temp_dir();
        $dumper = new QtFileDumper();
        $dumper->dump($catalogue, array('path' => $tempDir));
        $this->assertEquals(file_get_contents(__DIR__.'/../fixtures/resources.ts'), file_get_contents($tempDir.'/resources.en.ts'));
        unlink($tempDir.'/resources.en.ts');
    }
}
