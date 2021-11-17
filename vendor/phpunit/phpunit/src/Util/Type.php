<?php
class PHPUnit_Util_Type
{
    public static function isType($type)
    {
        return in_array(
            $type,
            array(
            'numeric',
            'integer',
            'int',
            'float',
            'string',
            'boolean',
            'bool',
            'null',
            'array',
            'object',
            'resource',
            'scalar'
            )
        );
    }
}
