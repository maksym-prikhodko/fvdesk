<?php
namespace Symfony\Component\Security\Core\Util;
class StringUtils
{
    private function __construct()
    {
    }
    public static function equals($knownString, $userInput)
    {
        if (!is_string($knownString)) {
            $knownString = (string) $knownString;
        }
        if (!is_string($userInput)) {
            $userInput = (string) $userInput;
        }
        if (function_exists('hash_equals')) {
            return hash_equals($knownString, $userInput);
        }
        $knownLen = self::safeStrlen($knownString);
        $userLen = self::safeStrlen($userInput);
        if ($userLen !== $knownLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $knownLen; $i++) {
            $result |= (ord($knownString[$i]) ^ ord($userInput[$i]));
        }
        return 0 === $result;
    }
    public static function safeStrlen($string)
    {
        static $funcExists = null;
        if (null === $funcExists) {
            $funcExists = function_exists('mb_strlen');
        }
        if ($funcExists) {
            return mb_strlen($string, '8bit');
        }
        return strlen($string);
    }
}
