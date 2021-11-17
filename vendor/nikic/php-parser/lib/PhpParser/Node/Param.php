<?php
namespace PhpParser\Node;
use PhpParser\Error;
use PhpParser\NodeAbstract;
class Param extends NodeAbstract
{
    public $type;
    public $byRef;
    public $variadic;
    public $name;
    public $default;
    public function __construct($name, Expr $default = null, $type = null, $byRef = false, $variadic = false, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->type = $type;
        $this->byRef = $byRef;
        $this->variadic = $variadic;
        $this->name = $name;
        $this->default = $default;
        if ($variadic && null !== $default) {
            throw new Error('Variadic parameter cannot have a default value');
        }
    }
    public function getSubNodeNames() {
        return array('type', 'byRef', 'variadic', 'name', 'default');
    }
}
