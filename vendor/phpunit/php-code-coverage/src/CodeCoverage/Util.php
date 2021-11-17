<?php
class PHP_CodeCoverage_Util
{
    public static function percent($a, $b, $asString = false, $fixedWidth = false)
    {
        if ($asString && $b == 0) {
            return '';
        }
        if ($b > 0) {
            $percent = ($a / $b) * 100;
        } else {
            $percent = 100;
        }
        if ($asString) {
            if ($fixedWidth) {
                return sprintf('%6.2F%%', $percent);
            }
            return sprintf('%01.2F%%', $percent);
        } else {
            return $percent;
        }
    }
}
