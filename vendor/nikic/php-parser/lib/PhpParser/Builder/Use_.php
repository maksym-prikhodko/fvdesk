<?php
namespace PhpParser\Builder;
use PhpParser\BuilderAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt;
class Use_ extends BuilderAbstract {
    protected $name;
    protected $type;
    protected $alias = null;
    public function __construct($name, $type) {
        $this->name = $this->normalizeName($name);
        $this->type = $type;
    }
    protected function as_($alias) {
        $this->alias = $alias;
        return $this;
    }
    public function __call($method, $args) {
        return call_user_func_array(array($this, $method . '_'), $args);
    }
    public function getNode() {
        $alias = null !== $this->alias ? $this->alias : $this->name->getLast();
        return new Stmt\Use_(array(
            new Stmt\UseUse($this->name, $alias)
        ), $this->type);
    }
}
