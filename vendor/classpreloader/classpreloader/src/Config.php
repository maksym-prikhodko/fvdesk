<?php
namespace ClassPreloader;
use ClassPreloader\Parser\AbstractNodeVisitor;
class Config implements \IteratorAggregate
{
    protected $visitors = array();
    protected $filenames = array();
    protected $exclusiveFilters = array();
    protected $inclusiveFilters = array();
    public function addFile($filename)
    {
        $this->filenames[] = $filename;
        return $this;
    }
    public function getFilenames()
    {
        $filenames = array();
        foreach ($this->filenames as $f) {
            foreach ($this->inclusiveFilters as $filter) {
                if (!preg_match($filter, $f)) {
                    continue 2;
                }
            }
            foreach ($this->exclusiveFilters as $filter) {
                if (preg_match($filter, $f)) {
                    continue 2;
                }
            }
            $filenames[] = $f;
        }
        return $filenames;
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->getFilenames());
    }
    public function addExclusiveFilter($pattern)
    {
        $this->exclusiveFilters[] = $pattern;
        return $this;
    }
    public function addInclusiveFilter($pattern)
    {
        $this->inclusiveFilters[] = $pattern;
        return $this;
    }
    public function addVisitor(AbstractNodeVisitor $visitor)
    {
        $this->visitors[] = $visitor;
        return $this;
    }
    public function getVisitors()
    {
        return $this->visitors;
    }
}
