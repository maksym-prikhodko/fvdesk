<?php
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class Use_ extends Stmt
{
    const TYPE_NORMAL   = 1;
    const TYPE_FUNCTION = 2;
    const TYPE_CONSTANT = 3;
    public $type;
    public $uses;
    public function __construct(array $uses, $type = self::TYPE_NORMAL, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->type = $type;
        $this->uses = $uses;
    }
    public function getSubNodeNames() {
        return array('type', 'uses');
    }
}
