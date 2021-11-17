<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
class Trait_ extends Declaration
{
    protected $name;
    protected $methods = array();
    public function __construct($name) {
        $this->name = $name;
    }
    public function addStmt($stmt) {
        $stmt = $this->normalizeNode($stmt);
        if (!$stmt instanceof Stmt\ClassMethod) {
            throw new \LogicException(sprintf('Unexpected node of type "%s"', $stmt->getType()));
        }
        $this->methods[] = $stmt;
        return $this;
    }
    public function getNode() {
        return new Stmt\Trait_($this->name, $this->methods, $this->attributes);
    }
}
