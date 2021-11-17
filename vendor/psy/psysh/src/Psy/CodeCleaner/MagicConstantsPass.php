<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\Node\Scalar\String as String;
class MagicConstantsPass extends CodeCleanerPass
{
    public function enterNode(Node $node)
    {
        if ($node instanceof Dir) {
            return new FuncCall(new Name('getcwd'), array(), $node->getAttributes());
        } elseif ($node instanceof File) {
            return new String('', $node->getAttributes());
        }
    }
}
