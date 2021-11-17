<?php
namespace Psy\Util;
class Json
{
    public static function encode($val, $opt = 0)
    {
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $opt |= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }
        return json_encode($val, $opt);
    }
}
