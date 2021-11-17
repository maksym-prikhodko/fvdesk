<?php
namespace ClassPreloader\Parser;
use ClassPreloader\Exception\SkipFileException;
use PhpParser\Node;
use PhpParser\Node\Scalar\MagicConst\File as FileNode;
use PhpParser\Node\Scalar\String_ as StringNode;
class FileVisitor extends AbstractNodeVisitor
{
    protected $skip = false;
    public function __construct($skip = false)
    {
        $this->skip = $skip;
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof FileNode) {
            if ($this->skip) {
                throw new SkipFileException('__FILE__ constant found, skipping...');
            }
            return new StringNode($this->getFilename());
        }
    }
}
