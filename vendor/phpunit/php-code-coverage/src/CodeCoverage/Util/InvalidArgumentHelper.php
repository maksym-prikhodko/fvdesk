<?php
class PHP_CodeCoverage_Util_InvalidArgumentHelper
{
    public static function factory($argument, $type, $value = null)
    {
        $stack = debug_backtrace(false);
        return new PHP_CodeCoverage_Exception(
            sprintf(
                'Argument #%d%sof %s::%s() must be a %s',
                $argument,
                $value !== null ? ' (' . gettype($value) . '#' . $value . ')' : ' (No Value) ',
                $stack[1]['class'],
                $stack[1]['function'],
                $type
            )
        );
    }
}
