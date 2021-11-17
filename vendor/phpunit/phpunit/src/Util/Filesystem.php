<?php
class PHPUnit_Util_Filesystem
{
    protected static $buffer = array();
    public static function classNameToFilename($className)
    {
        return str_replace(
            array('_', '\\'),
            DIRECTORY_SEPARATOR,
            $className
        ) . '.php';
    }
}
