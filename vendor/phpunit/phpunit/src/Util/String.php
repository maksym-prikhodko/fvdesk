<?php
class PHPUnit_Util_String
{
    public static function convertToUtf8($string)
    {
        if (!self::isUtf8($string)) {
            if (function_exists('mb_convert_encoding')) {
                $string = mb_convert_encoding($string, 'UTF-8');
            } else {
                $string = utf8_encode($string);
            }
        }
        return $string;
    }
    protected static function isUtf8($string)
    {
        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if (ord($string[$i]) < 0x80) {
                $n = 0;
            } elseif ((ord($string[$i]) & 0xE0) == 0xC0) {
                $n = 1;
            } elseif ((ord($string[$i]) & 0xF0) == 0xE0) {
                $n = 2;
            } elseif ((ord($string[$i]) & 0xF0) == 0xF0) {
                $n = 3;
            } else {
                return false;
            }
            for ($j = 0; $j < $n; $j++) {
                if ((++$i == $length) || ((ord($string[$i]) & 0xC0) != 0x80)) {
                    return false;
                }
            }
        }
        return true;
    }
}
