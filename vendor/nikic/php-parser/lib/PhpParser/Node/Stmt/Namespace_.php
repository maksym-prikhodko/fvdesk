<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Error;
class Namespace_ extends Node\Stmt
{
    public $name;
    public $stmts;
    protected static $specialNames = array(
        'self'   => true,
        'parent' => true,
        'static' => true,
    );
    public function __construct(Node\Name $name = null, $stmts = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
        $this->stmts = $stmts;
        if (isset(self::$specialNames[(string) $this->name])) {
            throw new Error(sprintf('Cannot use \'%s\' as namespace name', $this->name));
        }
        if (null !== $this->stmts) {
            foreach ($this->stmts as $stmt) {
                if ($stmt instanceof self) {
                    throw new Error('Namespace declarations cannot be nested', $stmt->getLine());
                }
            }
        }
    }
    public function getSubNodeNames() {
        return array('name', 'stmts');
    }
}
