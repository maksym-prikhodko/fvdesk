<?php
class PHPUnit_Util_Regex
{
    public static function pregMatchSafe($pattern, $subject, $matches = null, $flags = 0, $offset = 0)
    {
        $handler_terminator = PHPUnit_Util_ErrorHandler::handleErrorOnce(E_WARNING);
        $match = preg_match($pattern, $subject, $matches, $flags, $offset);
        $handler_terminator(); 
        return $match;
    }
}
