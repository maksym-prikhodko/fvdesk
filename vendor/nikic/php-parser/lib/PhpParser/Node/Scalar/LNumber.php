<?php
namespace PhpParser\Node\Scalar;
use PhpParser\Node\Scalar;
class LNumber extends Scalar
{
    public $value;
    public function __construct($value = 0, array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->value = $value;
    }
    public function getSubNodeNames() {
        return array('value');
    }
    public static function parse($str) {
        if ('0' === $str) {
            return 0;
        }
        if ('0' === $str[0]) {
            if ('x' === $str[1] || 'X' === $str[1]) {
                return hexdec($str);
            }
            if ('b' === $str[1] || 'B' === $str[1]) {
                return bindec($str);
            }
            return intval($str, 8);
        }
        return (int) $str;
    }
}
