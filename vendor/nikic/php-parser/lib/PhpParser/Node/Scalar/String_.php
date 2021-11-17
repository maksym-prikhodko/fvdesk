<?php
namespace PhpParser\Node\Scalar;
use PhpParser\Node\Scalar;
class String_ extends Scalar
{
    public $value;
    protected static $replacements = array(
        '\\' => '\\',
        '$'  =>  '$',
        'n'  => "\n",
        'r'  => "\r",
        't'  => "\t",
        'f'  => "\f",
        'v'  => "\v",
        'e'  => "\x1B",
    );
    public function __construct($value = '', array $attributes = array()) {
        parent::__construct(null, $attributes);
        $this->value = $value;
    }
    public function getSubNodeNames() {
        return array('value');
    }
    public static function parse($str) {
        $bLength = 0;
        if ('b' === $str[0]) {
            $bLength = 1;
        }
        if ('\'' === $str[$bLength]) {
            return str_replace(
                array('\\\\', '\\\''),
                array(  '\\',   '\''),
                substr($str, $bLength + 1, -1)
            );
        } else {
            return self::parseEscapeSequences(substr($str, $bLength + 1, -1), '"');
        }
    }
    public static function parseEscapeSequences($str, $quote) {
        if (null !== $quote) {
            $str = str_replace('\\' . $quote, $quote, $str);
        }
        return preg_replace_callback(
            '~\\\\([\\\\$nrtfve]|[xX][0-9a-fA-F]{1,2}|[0-7]{1,3})~',
            array(__CLASS__, 'parseCallback'),
            $str
        );
    }
    private static function parseCallback($matches) {
        $str = $matches[1];
        if (isset(self::$replacements[$str])) {
            return self::$replacements[$str];
        } elseif ('x' === $str[0] || 'X' === $str[0]) {
            return chr(hexdec($str));
        } else {
            return chr(octdec($str));
        }
    }
    public static function parseDocString($startToken, $str) {
        $str = preg_replace('~(\r\n|\n|\r)$~', '', $str);
        if (false !== strpos($startToken, '\'')) {
            return $str;
        }
        return self::parseEscapeSequences($str, null);
    }
}
