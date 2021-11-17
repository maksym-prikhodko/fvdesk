<?php
namespace Prophecy\Doubler\ClassPatch;
use phpDocumentor\Reflection\DocBlock;
use Prophecy\Doubler\Generator\Node\ClassNode;
use Prophecy\Doubler\Generator\Node\MethodNode;
class MagicCallPatch implements ClassPatchInterface
{
    public function supports(ClassNode $node)
    {
        return true;
    }
    public function apply(ClassNode $node)
    {
        $parentClass = $node->getParentClass();
        $reflectionClass = new \ReflectionClass($parentClass);
        $phpdoc = new DocBlock($reflectionClass->getDocComment());
        $tagList = $phpdoc->getTagsByName('method');
        foreach($tagList as $tag) {
            $methodName = $tag->getMethodName();
            if (!$reflectionClass->hasMethod($methodName)) {
                $methodNode = new MethodNode($tag->getMethodName());
                $methodNode->setStatic($tag->isStatic());
                $node->addMethod($methodNode);
            }
        }
    }
    public function getPriority()
    {
        return 50;
    }
}
