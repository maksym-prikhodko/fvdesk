<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Error;
class Interface_ extends ClassLike
{
    public $extends;
    protected static $specialNames = array(
        'self'   => true,
        'parent' => true,
        'static' => true,
    );
    public function __construct($name, array $subNodes = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->name = $name;
        $this->extends = isset($subNodes['extends']) ? $subNodes['extends'] : array();
        $this->stmts = isset($subNodes['stmts']) ? $subNodes['stmts'] : array();
        if (isset(self::$specialNames[(string) $this->name])) {
            throw new Error(sprintf('Cannot use \'%s\' as class name as it is reserved', $this->name));
        }
        foreach ($this->extends as $interface) {
            if (isset(self::$specialNames[(string) $interface])) {
                throw new Error(sprintf('Cannot use \'%s\' as interface name as it is reserved', $interface));
            }
        }
    }
    public function getSubNodeNames() {
        return array('name', 'extends', 'stmts');
    }
}
