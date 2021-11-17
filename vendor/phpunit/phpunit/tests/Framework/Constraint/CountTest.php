<?php
class CountTest extends PHPUnit_Framework_TestCase
{
    public function testCount()
    {
        $countConstraint = new PHPUnit_Framework_Constraint_Count(3);
        $this->assertTrue($countConstraint->evaluate(array(1, 2, 3), '', true));
        $countConstraint = new PHPUnit_Framework_Constraint_Count(0);
        $this->assertTrue($countConstraint->evaluate(array(), '', true));
        $countConstraint = new PHPUnit_Framework_Constraint_Count(2);
        $it = new TestIterator(array(1, 2));
        $this->assertTrue($countConstraint->evaluate($it, '', true));
    }
    public function testCountDoesNotChangeIteratorKey()
    {
        $countConstraint = new PHPUnit_Framework_Constraint_Count(2);
        $it = new TestIterator(array(1, 2));
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(1, $it->current());
        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(2, $it->current());
        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertFalse($it->valid());
        $it = new TestIterator2(array(1, 2));
        $countConstraint = new PHPUnit_Framework_Constraint_Count(2);
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(1, $it->current());
        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(2, $it->current());
        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertFalse($it->valid());
    }
}
