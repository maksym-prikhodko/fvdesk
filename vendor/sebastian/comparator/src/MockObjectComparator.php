<?php
namespace SebastianBergmann\Comparator;
class MockObjectComparator extends ObjectComparator
{
    public function accepts($expected, $actual)
    {
        return $expected instanceof \PHPUnit_Framework_MockObject_MockObject && $actual instanceof \PHPUnit_Framework_MockObject_MockObject;
    }
    protected function toArray($object)
    {
        $array = parent::toArray($object);
        unset($array['__phpunit_invocationMocker']);
        return $array;
    }
}