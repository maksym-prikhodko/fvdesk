<?php
namespace ClassPreloader\Parser;
use PhpParser\NodeTraverser as BaseTraverser;
class NodeTraverser extends BaseTraverser
{
    public function traverseFile(array $nodes, $filename)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor instanceof AbstractNodeVisitor) {
                $visitor->setFilename($filename);
            }
        }
        return $this->traverse($nodes);
    }
}
