<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
class Class_ extends Declaration
{
    protected $name;
    protected $extends = null;
    protected $implements = array();
    protected $type = 0;
    protected $uses = array();
    protected $constants = array();
    protected $properties = array();
    protected $methods = array();
    public function __construct($name) {
        $this->name = $name;
    }
    public function extend($class) {
        $this->extends = $this->normalizeName($class);
        return $this;
    }
    public function implement() {
        foreach (func_get_args() as $interface) {
            $this->implements[] = $this->normalizeName($interface);
        }
        return $this;
    }
    public function makeAbstract() {
        $this->setModifier(Stmt\Class_::MODIFIER_ABSTRACT);
        return $this;
    }
    public function makeFinal() {
        $this->setModifier(Stmt\Class_::MODIFIER_FINAL);
        return $this;
    }
    public function addStmt($stmt) {
        $stmt = $this->normalizeNode($stmt);
        $targets = array(
            'Stmt_TraitUse'    => &$this->uses,
            'Stmt_ClassConst'  => &$this->constants,
            'Stmt_Property'    => &$this->properties,
            'Stmt_ClassMethod' => &$this->methods,
        );
        $type = $stmt->getType();
        if (!isset($targets[$type])) {
            throw new \LogicException(sprintf('Unexpected node of type "%s"', $type));
        }
        $targets[$type][] = $stmt;
        return $this;
    }
    public function getNode() {
        return new Stmt\Class_($this->name, array(
            'type' => $this->type,
            'extends' => $this->extends,
            'implements' => $this->implements,
            'stmts' => array_merge($this->uses, $this->constants, $this->properties, $this->methods),
        ), $this->attributes);
    }
}
