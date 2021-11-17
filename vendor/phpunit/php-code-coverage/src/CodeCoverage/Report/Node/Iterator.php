<?php
class PHP_CodeCoverage_Report_Node_Iterator implements RecursiveIterator
{
    protected $position;
    protected $nodes;
    public function __construct(PHP_CodeCoverage_Report_Node_Directory $node)
    {
        $this->nodes = $node->getChildNodes();
    }
    public function rewind()
    {
        $this->position = 0;
    }
    public function valid()
    {
        return $this->position < count($this->nodes);
    }
    public function key()
    {
        return $this->position;
    }
    public function current()
    {
        return $this->valid() ? $this->nodes[$this->position] : null;
    }
    public function next()
    {
        $this->position++;
    }
    public function getChildren()
    {
        return new PHP_CodeCoverage_Report_Node_Iterator(
            $this->nodes[$this->position]
        );
    }
    public function hasChildren()
    {
        return $this->nodes[$this->position] instanceof PHP_CodeCoverage_Report_Node_Directory;
    }
}
