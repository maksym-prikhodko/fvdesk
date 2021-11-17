<?php
class PHPUnit_Framework_Constraint_Count extends PHPUnit_Framework_Constraint
{
    protected $expectedCount = 0;
    public function __construct($expected)
    {
        parent::__construct();
        $this->expectedCount = $expected;
    }
    protected function matches($other)
    {
        return $this->expectedCount === $this->getCountOf($other);
    }
    protected function getCountOf($other)
    {
        if ($other instanceof Countable || is_array($other)) {
            return count($other);
        } elseif ($other instanceof Traversable) {
            if ($other instanceof IteratorAggregate) {
                $iterator = $other->getIterator();
            } else {
                $iterator = $other;
            }
            $key = $iterator->key();
            $count = iterator_count($iterator);
            if ($key !== null) {
                $iterator->rewind();
                while ($iterator->valid() && $key !== $iterator->key()) {
                    $iterator->next();
                }
            }
            return $count;
        }
    }
    protected function failureDescription($other)
    {
        return sprintf(
            'actual size %d matches expected size %d',
            $this->getCountOf($other),
            $this->expectedCount
        );
    }
    public function toString()
    {
        return sprintf(
            'count matches %d',
            $this->expectedCount
        );
    }
}
