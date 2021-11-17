<?php
class PHPUnit_Runner_Filter_Factory
{
    private $filters = array();
    public function addFilter(ReflectionClass $filter, $args)
    {
        if (!$filter->isSubclassOf('RecursiveFilterIterator')) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class "%s" does not extend RecursiveFilterIterator',
                    $filter->name
                )
            );
        }
        $this->filters[] = array($filter, $args);
    }
    public function factory(Iterator $iterator, PHPUnit_Framework_TestSuite $suite)
    {
        foreach ($this->filters as $filter) {
            list($class, $args) = $filter;
            $iterator = $class->newInstance($iterator, $args, $suite);
        }
        return $iterator;
    }
}
