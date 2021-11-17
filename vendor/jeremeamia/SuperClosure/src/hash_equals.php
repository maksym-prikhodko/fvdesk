<?php
if (!function_exists('hash_equals')) {
    function hash_equals($knownString, $userString)
    {
        $argc = func_num_args();
        if ($argc < 2) {
            trigger_error(
                "hash_equals() expects at least 2 parameters, {$argc} given",
                E_USER_WARNING
            );
            return null;
        }
        if (!is_string($knownString)) {
            trigger_error(sprintf(
                "hash_equals(): Expected known_string to be a string, %s given",
                gettype($knownString)
            ), E_USER_WARNING);
            return false;
        }
        if (!is_string($userString)) {
            trigger_error(sprintf(
                "hash_equals(): Expected user_string to be a string, %s given",
                gettype($knownString)
            ), E_USER_WARNING);
            return false;
        }
        if (strlen($knownString) !== strlen($userString)) {
            return false;
        }
        $len = strlen($knownString);
        $result = 0;
        for ($i = 0; $i < $len; $i++) {
            $result |= (ord($knownString[$i]) ^ ord($userString[$i]));
        }
        return 0 === $result;
    }
}
