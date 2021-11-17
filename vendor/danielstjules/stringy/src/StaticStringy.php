<?php
namespace Stringy;
class StaticStringy
{
    public static function chars($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->chars();
    }
    public static function upperCaseFirst($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->upperCaseFirst();
    }
    public static function lowerCaseFirst($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->lowerCaseFirst();
    }
    public static function camelize($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->camelize();
    }
    public static function upperCamelize($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->upperCamelize();
    }
    public static function dasherize($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->dasherize();
    }
    public static function underscored($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->underscored();
    }
    public static function swapCase($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->swapCase();
    }
    public static function titleize($str, $ignore = null, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->titleize($ignore);
    }
    public static function humanize($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->humanize();
    }
    public static function tidy($str)
    {
        return (string) Stringy::create($str)->tidy();
    }
    public static function collapseWhitespace($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->collapseWhitespace();
    }
    public static function toAscii($str, $removeUnsupported = true)
    {
        return (string) Stringy::create($str)->toAscii($removeUnsupported);
    }
    public static function pad($str, $length, $padStr = ' ', $padType = 'right',
                               $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->pad($length, $padStr, $padType);
    }
    public static function padLeft($str, $length, $padStr = ' ', $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->padLeft($length, $padStr);
    }
    public static function padRight($str, $length, $padStr = ' ', $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->padRight($length, $padStr);
    }
    public static function padBoth($str, $length, $padStr = ' ', $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->padBoth($length, $padStr);
    }
    public static function startsWith($str, $substring, $caseSensitive = true,
                                      $encoding = null)
    {
        return Stringy::create($str, $encoding)
            ->startsWith($substring, $caseSensitive);
    }
    public static function endsWith($str, $substring, $caseSensitive = true,
                                    $encoding = null)
    {
        return Stringy::create($str, $encoding)
            ->endsWith($substring, $caseSensitive);
    }
    public static function toSpaces($str, $tabLength = 4)
    {
        return (string) Stringy::create($str)->toSpaces($tabLength);
    }
    public static function toTabs($str, $tabLength = 4)
    {
        return (string) Stringy::create($str)->toTabs($tabLength);
    }
    public static function toLowerCase($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->toLowerCase();
    }
    public static function toTitleCase($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->toTitleCase();
    }
    public static function toUpperCase($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->toUpperCase();
    }
    public static function slugify($str, $replacement = '-')
    {
        return (string) Stringy::create($str)->slugify($replacement);
    }
    public static function contains($haystack, $needle, $caseSensitive = true,
                                    $encoding = null)
    {
        return Stringy::create($haystack, $encoding)
            ->contains($needle, $caseSensitive);
    }
    public static function containsAny($haystack, $needles,
                                       $caseSensitive = true, $encoding = null)
    {
        return Stringy::create($haystack, $encoding)
            ->containsAny($needles, $caseSensitive);
    }
    public static function containsAll($haystack, $needles,
                                       $caseSensitive = true, $encoding = null)
    {
        return Stringy::create($haystack, $encoding)
            ->containsAll($needles, $caseSensitive);
    }
    public static function surround($str, $substring)
    {
        return (string) Stringy::create($str)->surround($substring);
    }
    public static function insert($str, $substring, $index, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->insert($substring, $index);
    }
    public static function truncate($str, $length, $substring = '',
                                    $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->truncate($length, $substring);
    }
    public static function safeTruncate($str, $length, $substring = '',
                                        $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->safeTruncate($length, $substring);
    }
    public static function reverse($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->reverse();
    }
    public static function shuffle($str, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->shuffle();
    }
    public static function trim($str)
    {
        return trim($str);
    }
    public static function longestCommonPrefix($str, $otherStr, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->longestCommonPrefix($otherStr);
    }
    public static function longestCommonSuffix($str, $otherStr, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->longestCommonSuffix($otherStr);
    }
    public static function longestCommonSubstring($str, $otherStr,
                                                  $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->longestCommonSubstring($otherStr);
    }
    public static function length($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->length();
    }
    public static function substr($str, $start, $length = null, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->substr($start, $length);
    }
    public static function at($str, $index, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->at($index);
    }
    public static function first($str, $n, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->first($n);
    }
    public static function last($str, $n, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->last($n);
    }
    public static function ensureLeft($str, $substring, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->ensureLeft($substring);
    }
    public static function ensureRight($str, $substring, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->ensureRight($substring);
    }
    public static function removeLeft($str, $substring, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->removeLeft($substring);
    }
    public static function removeRight($str, $substring, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)->removeRight($substring);
    }
    public static function hasLowerCase($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->hasLowerCase();
    }
    public static function hasUpperCase($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->hasUpperCase();
    }
    public static function isAlpha($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->isAlpha();
    }
    public static function isAlphanumeric($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->isAlphanumeric();
    }
    public static function isBlank($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->isBlank();
    }
    public static function isJson($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->isJson();
    }
    public static function isLowerCase($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->isLowerCase();
    }
    public static function isSerialized($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->isSerialized();
    }
    public static function isUpperCase($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->isUpperCase();
    }
    public static function isHexadecimal($str, $encoding = null)
    {
        return Stringy::create($str, $encoding)->isHexadecimal();
    }
    public static function countSubstr($str, $substring, $caseSensitive = true,
                                       $encoding = null)
    {
        return Stringy::create($str, $encoding)
            ->countSubstr($substring, $caseSensitive);
    }
    public static function replace($str, $search, $replacement, $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->replace($search, $replacement);
    }
    public static function regexReplace($str, $pattern, $replacement,
                                        $options = 'msr', $encoding = null)
    {
        return (string) Stringy::create($str, $encoding)
            ->regexReplace($pattern, $replacement, $options, $encoding);
    }
}
