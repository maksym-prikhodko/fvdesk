<?php
namespace SebastianBergmann\Comparator;
class DateTimeComparator extends ObjectComparator
{
    public function accepts($expected, $actual)
    {
        return $expected instanceof \DateTime && $actual instanceof \DateTime;
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $delta = new \DateInterval(sprintf('PT%sS', abs($delta)));
        $expectedLower = clone $expected;
        $expectedUpper = clone $expected;
        if ($actual < $expectedLower->sub($delta) ||
            $actual > $expectedUpper->add($delta)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->dateTimeToString($expected),
                $this->dateTimeToString($actual),
                false,
                'Failed asserting that two DateTime objects are equal.'
            );
        }
    }
    protected function dateTimeToString(\DateTime $datetime)
    {
        $string = $datetime->format(\DateTime::ISO8601);
        return $string ? $string : 'Invalid DateTime object';
    }
}
