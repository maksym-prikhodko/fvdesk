<?php
namespace SebastianBergmann\Environment;
use PHPUnit_Framework_TestCase;
class ConsoleTest extends PHPUnit_Framework_TestCase
{
    private $console;
    protected function setUp()
    {
        $this->console = new Console;
    }
    public function testCanDetectIfStdoutIsInteractiveByDefault()
    {
        $this->assertInternalType('boolean', $this->console->isInteractive());
    }
    public function testCanDetectIfFileDescriptorIsInteractive()
    {
        $this->assertInternalType('boolean', $this->console->isInteractive(STDOUT));
    }
    public function testCanDetectColorSupport()
    {
        $this->assertInternalType('boolean', $this->console->hasColorSupport());
    }
    public function testCanDetectNumberOfColumns()
    {
        $this->assertInternalType('integer', $this->console->getNumberOfColumns());
    }
}
