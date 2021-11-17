<?php
namespace Psy\Test\Readline;
use Psy\Readline\Transient;
class TransientTest extends \PHPUnit_Framework_TestCase
{
    public function testHistory()
    {
        $readline = new Transient();
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $this->assertEquals(array('foo'), $readline->listHistory());
        $readline->addHistory('bar');
        $this->assertEquals(array('foo', 'bar'), $readline->listHistory());
        $readline->addHistory('baz');
        $this->assertEquals(array('foo', 'bar', 'baz'), $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }
    public function testHistorySize()
    {
        $readline = new Transient(null, 2);
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $readline->addHistory('bar');
        $this->assertEquals(array('foo', 'bar'), $readline->listHistory());
        $readline->addHistory('baz');
        $this->assertEquals(array('bar', 'baz'), $readline->listHistory());
        $readline->addHistory('w00t');
        $this->assertEquals(array('baz', 'w00t'), $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }
    public function testHistoryEraseDups()
    {
        $readline = new Transient(null, 0, true);
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $readline->addHistory('bar');
        $readline->addHistory('foo');
        $this->assertEquals(array('bar', 'foo'), $readline->listHistory());
        $readline->addHistory('baz');
        $readline->addHistory('w00t');
        $readline->addHistory('baz');
        $this->assertEquals(array('bar', 'foo', 'w00t', 'baz'), $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }
    public function testSomeThingsAreAlwaysTrue()
    {
        $readline = new Transient();
        $this->assertTrue(Transient::isSupported());
        $this->assertTrue($readline->readHistory());
        $this->assertTrue($readline->writeHistory());
    }
}
