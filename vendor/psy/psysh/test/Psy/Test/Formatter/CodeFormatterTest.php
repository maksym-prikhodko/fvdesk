<?php
namespace Psy\Test\Formatter;
use Psy\Formatter\CodeFormatter;
class CodeFormatterTest extends \PHPUnit_Framework_TestCase
{
    private function ignoreThisMethod($arg)
    {
        echo "whot!";
    }
    public function testFormat()
    {
        $expected = <<<EOS
  > 18|     private function ignoreThisMethod(\$arg)
    19|     {
    20|         echo "whot!";
    21|     }
EOS;
        $formatted = CodeFormatter::format(new \ReflectionMethod($this, 'ignoreThisMethod'));
        $formattedWithoutColors = preg_replace('#' . chr(27) . '\[\d\d?m#', '', $formatted);
        $this->assertEquals($expected, rtrim($formattedWithoutColors));
        $this->assertNotEquals($expected, rtrim($formatted));
    }
    public function testCodeFormatterThrowsException($filename)
    {
        $reflector = $this->getMockBuilder('ReflectionClass')
            ->disableOriginalConstructor()
            ->getMock();
        $reflector
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue($filename));
        CodeFormatter::format($reflector);
    }
    public function filenames()
    {
        return array(array(null), array('not a file'));
    }
}
