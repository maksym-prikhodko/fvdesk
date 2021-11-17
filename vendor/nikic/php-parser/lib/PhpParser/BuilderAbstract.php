<?php
namespace PhpParser;
use PhpParser\Node\Name;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Scalar;
use PhpParser\Comment;
abstract class BuilderAbstract implements Builder {
    protected function normalizeNode($node) {
        if ($node instanceof Builder) {
            return $node->getNode();
        } elseif ($node instanceof Node) {
            return $node;
        }
        throw new \LogicException('Expected node or builder object');
    }
    protected function normalizeName($name) {
        if ($name instanceof Name) {
            return $name;
        } elseif (is_string($name)) {
            if (!$name) {
                throw new \LogicException('Name cannot be empty');
            }
            if ($name[0] == '\\') {
                return new Name\FullyQualified(substr($name, 1));
            } elseif (0 === strpos($name, 'namespace\\')) {
                return new Name\Relative(substr($name, strlen('namespace\\')));
            } else {
                return new Name($name);
            }
        }
        throw new \LogicException('Name must be a string or an instance of PhpParser\Node\Name');
    }
    protected function normalizeValue($value) {
        if ($value instanceof Node) {
            return $value;
        } elseif (is_null($value)) {
            return new Expr\ConstFetch(
                new Name('null')
            );
        } elseif (is_bool($value)) {
            return new Expr\ConstFetch(
                new Name($value ? 'true' : 'false')
            );
        } elseif (is_int($value)) {
            return new Scalar\LNumber($value);
        } elseif (is_float($value)) {
            return new Scalar\DNumber($value);
        } elseif (is_string($value)) {
            return new Scalar\String_($value);
        } elseif (is_array($value)) {
            $items = array();
            $lastKey = -1;
            foreach ($value as $itemKey => $itemValue) {
                if (null !== $lastKey && ++$lastKey === $itemKey) {
                    $items[] = new Expr\ArrayItem(
                        $this->normalizeValue($itemValue)
                    );
                } else {
                    $lastKey = null;
                    $items[] = new Expr\ArrayItem(
                        $this->normalizeValue($itemValue),
                        $this->normalizeValue($itemKey)
                    );
                }
            }
            return new Expr\Array_($items);
        } else {
            throw new \LogicException('Invalid value');
        }
    }
    protected function normalizeDocComment($docComment) {
        if ($docComment instanceof Comment\Doc) {
            return $docComment;
        } else if (is_string($docComment)) {
            return new Comment\Doc($docComment);
        } else {
            throw new \LogicException('Doc comment must be a string or an instance of PhpParser\Comment\Doc');
        }
    }
    protected function setModifier($modifier) {
        Stmt\Class_::verifyModifier($this->type, $modifier);
        $this->type |= $modifier;
    }
}
