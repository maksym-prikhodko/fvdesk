<?php
namespace ClassPreloader\Parser;
use PhpParser\NodeVisitorAbstract;
abstract class AbstractNodeVisitor extends NodeVisitorAbstract
{
    protected $filename = '';
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }
    public function getFilename()
    {
        return $this->filename;
    }
    public function getDir()
    {
        return dirname($this->getFilename());
    }
}
