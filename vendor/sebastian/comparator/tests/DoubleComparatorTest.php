<?php
namespace SebastianBergmann\Comparator;
class DoubleComparatorTest extends \PHPUnit_Framework_TestCase
{
    private $comparator;
    protected function setUp()
    {
        $this->comparator = new DoubleComparator;
    }
    public function acceptsSucceedsProvider()
    {
        return array(
          array(0, 5.0),
          array(5.0, 0),
          array('5', 4.5),
          array(1.2e3, 7E-10),
          array(3, acos(8)),
          array(acos(8), 3),
          array(acos(8), acos(8))
        );
    }
    public function acceptsFailsProvider()
    {
        return array(
          array(5, 5),
          array('4.5', 5),
          array(0x539, 02471),
          array(5.0, false),
          array(null, 5.0)
        );
    }
    public function assertEqualsSucceedsProvider()
    {
        return array(
          array(2.3, 2.3),
          array('2.3', 2.3),
          array(5.0, 5),
          array(5, 5.0),
          array(5.0, '5'),
          array(1.2e3, 1200),
          array(2.3, 2.5, 0.5),
          array(3, 3.05, 0.05),
          array(1.2e3, 1201, 1),
          array((string)(1/3), 1 - 2/3),
          array(1/3, (string)(1 - 2/3))
        );
    }
    public function assertEqualsFailsProvider()
    {
        return array(
          array(2.3, 4.2),
          array('2.3', 4.2),
          array(5.0, '4'),
          array(5.0, 6),
          array(1.2e3, 1201),
          array(2.3, 2.5, 0.2),
          array(3, 3.05, 0.04),
          array(3, acos(8)),
          array(acos(8), 3),
          array(acos(8), acos(8))
        );
    }
    public function testAcceptsSucceeds($expected, $actual)
    {
        $this->assertTrue(
          $this->comparator->accepts($expected, $actual)
        );
    }
    public function testAcceptsFails($expected, $actual)
    {
        $this->assertFalse(
          $this->comparator->accepts($expected, $actual)
        );
    }
    public function testAssertEqualsSucceeds($expected, $actual, $delta = 0.0)
    {
        $exception = null;
        try {
            $this->comparator->assertEquals($expected, $actual, $delta);
        }
        catch (ComparisonFailure $exception) {
        }
        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }
    public function testAssertEqualsFails($expected, $actual, $delta = 0.0)
    {
        $this->setExpectedException(
          'SebastianBergmann\\Comparator\\ComparisonFailure', 'matches expected'
        );
        $this->comparator->assertEquals($expected, $actual, $delta);
    }
}