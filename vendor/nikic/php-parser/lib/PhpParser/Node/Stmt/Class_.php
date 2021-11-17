<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Error;
class Class_ extends ClassLike
{
    const MODIFIER_PUBLIC    =  1;
    const MODIFIER_PROTECTED =  2;
    const MODIFIER_PRIVATE   =  4;
    const MODIFIER_STATIC    =  8;
    const MODIFIER_ABSTRACT  = 16;
    const MODIFIER_FINAL     = 32;
    const VISIBILITY_MODIFER_MASK = 7; 
    public $type;
    public $extends;
    public $implements;
    protected static $specialNames = array(
        'self'   => true,
        'parent' => true,
        'static' => true,
    );
    public function __construct($name, array $subNodes = array(), array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->type = isset($subNodes['type']) ? $subNodes['type'] : 0;
        $this->name = $name;
        $this->extends = isset($subNodes['extends']) ? $subNodes['extends'] : null;
        $this->implements = isset($subNodes['implements']) ? $subNodes['implements'] : array();
        $this->stmts = isset($subNodes['stmts']) ? $subNodes['stmts'] : array();
        if (isset(self::$specialNames[(string) $this->name])) {
            throw new Error(sprintf('Cannot use \'%s\' as class name as it is reserved', $this->name));
        }
        if (isset(self::$specialNames[(string) $this->extends])) {
            throw new Error(sprintf('Cannot use \'%s\' as class name as it is reserved', $this->extends));
        }
        foreach ($this->implements as $interface) {
            if (isset(self::$specialNames[(string) $interface])) {
                throw new Error(sprintf('Cannot use \'%s\' as interface name as it is reserved', $interface));
            }
        }
    }
    public function getSubNodeNames() {
        return array('type', 'name', 'extends', 'implements', 'stmts');
    }
    public function isAbstract() {
        return (bool) ($this->type & self::MODIFIER_ABSTRACT);
    }
    public function isFinal() {
        return (bool) ($this->type & self::MODIFIER_FINAL);
    }
    public static function verifyModifier($a, $b) {
        if ($a & self::VISIBILITY_MODIFER_MASK && $b & self::VISIBILITY_MODIFER_MASK) {
            throw new Error('Multiple access type modifiers are not allowed');
        }
        if ($a & self::MODIFIER_ABSTRACT && $b & self::MODIFIER_ABSTRACT) {
            throw new Error('Multiple abstract modifiers are not allowed');
        }
        if ($a & self::MODIFIER_STATIC && $b & self::MODIFIER_STATIC) {
            throw new Error('Multiple static modifiers are not allowed');
        }
        if ($a & self::MODIFIER_FINAL && $b & self::MODIFIER_FINAL) {
            throw new Error('Multiple final modifiers are not allowed');
        }
        if ($a & 48 && $b & 48) {
            throw new Error('Cannot use the final modifier on an abstract class member');
        }
    }
}
