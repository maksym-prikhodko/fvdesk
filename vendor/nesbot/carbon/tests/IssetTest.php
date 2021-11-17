<?php
use Carbon\Carbon;
class IssetTest extends TestFixture
{
    public function testIssetReturnFalseForUnknownProperty()
    {
        $this->assertFalse(isset(Carbon::create(1234, 5, 6, 7, 8, 9)->sdfsdfss));
    }
    public function testIssetReturnTrueForProperties()
    {
        $properties = array(
            'year',
            'month',
            'day',
            'hour',
            'minute',
            'second',
            'dayOfWeek',
            'dayOfYear',
            'daysInMonth',
            'timestamp',
            'age',
            'quarter',
            'dst',
            'offset',
            'offsetHours',
            'timezone',
            'timezoneName',
            'tz',
            'tzName',
        );
        foreach ($properties as $property) {
            $this->assertTrue(isset(Carbon::create(1234, 5, 6, 7, 8, 9)->$property));
        }
    }
}
