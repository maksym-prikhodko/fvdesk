<?php
namespace PhpParser;
interface NodeTraverserInterface
{
    const DONT_TRAVERSE_CHILDREN = 1;
    const REMOVE_NODE = false;
    function addVisitor(NodeVisitor $visitor);
    function removeVisitor(NodeVisitor $visitor);
    function traverse(array $nodes);
}
