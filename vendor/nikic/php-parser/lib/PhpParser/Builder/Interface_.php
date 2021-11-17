<?php
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
class Interface_ extends Declaration
{
    protected $name;
    protected $extends = array();
    protected $constants = array();
    protected $methods = array();
    public function __construct($name) {
        $this->name = $name;
    }
    public function extend() {
        foreach (func_get_args() as $interface) {
            $this->extends[] = $this->normalizeName($interface);
        }
        return $this;
    }
    public function addStmt($stmt) {
        $stmt = $this->normalizeNode($stmt);
        $type = $stmt->getType();
        switch ($type) {
            case 'Stmt_ClassConst':
                $this->constants[] = $stmt;
                break;
            case 'Stmt_ClassMethod':
                $stmt->stmts = null;
                $this->methods[] = $stmt;
                break;
            default:
                throw new \LogicException(sprintf('Unexpected node of type "%s"', $type));
        }
        return $this;
    }
    public function getNode() {
        return new Stmt\Interface_($this->name, array(
            'extends' => $this->extends,
            'stmts' => array_merge($this->constants, $this->methods),
        ), $this->attributes);
    }
}
