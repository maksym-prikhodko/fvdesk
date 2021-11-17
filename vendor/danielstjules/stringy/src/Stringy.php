<?php
namespace Stringy;
class Stringy implements \Countable, \IteratorAggregate, \ArrayAccess
{
    protected $str;
    protected $encoding;
    public function __construct($str, $encoding = null)
    {
        if (is_array($str)) {
            throw new \InvalidArgumentException(
                'Passed value cannot be an array'
            );
        } elseif (is_object($str) && !method_exists($str, '__toString')) {
            throw new \InvalidArgumentException(
                'Passed object must have a __toString method'
            );
        }
        $this->str = (string) $str;
        $this->encoding = $encoding ?: mb_internal_encoding();
    }
    public static function create($str, $encoding = null)
    {
        return new static($str, $encoding);
    }
    public function __toString()
    {
        return $this->str;
    }
    public function getEncoding()
    {
        return $this->encoding;
    }
    public function count()
    {
        return $this->length();
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->chars());
    }
    public function offsetExists($offset)
    {
        $length = $this->length();
        $offset = (int) $offset;
        if ($offset >= 0) {
            return ($length > $offset);
        }
        return ($length >= abs($offset));
    }
    public function offsetGet($offset)
    {
        $offset = (int) $offset;
        $length = $this->length();
        if (($offset >= 0 && $length <= $offset) || $length < abs($offset)) {
            throw new \OutOfBoundsException('No character exists at the index');
        }
        return mb_substr($this->str, $offset, 1, $this->encoding);
    }
    public function offsetSet($offset, $value)
    {
        throw new \Exception('Stringy object is immutable, cannot modify char');
    }
    public function offsetUnset($offset)
    {
        throw new \Exception('Stringy object is immutable, cannot unset char');
    }
    public function chars()
    {
        $chars = array();
        for ($i = 0, $l = $this->length(); $i < $l; $i++) {
            $chars[] = $this->at($i)->str;
        }
        return $chars;
    }
    public function upperCaseFirst()
    {
        $first = mb_substr($this->str, 0, 1, $this->encoding);
        $rest = mb_substr($this->str, 1, $this->length() - 1,
            $this->encoding);
        $str = mb_strtoupper($first, $this->encoding) . $rest;
        return static::create($str, $this->encoding);
    }
    public function lowerCaseFirst()
    {
        $first = mb_substr($this->str, 0, 1, $this->encoding);
        $rest = mb_substr($this->str, 1, $this->length() - 1,
            $this->encoding);
        $str = mb_strtolower($first, $this->encoding) . $rest;
        return static::create($str, $this->encoding);
    }
    public function camelize()
    {
        $encoding = $this->encoding;
        $stringy = $this->trim()->lowerCaseFirst();
        $camelCase = preg_replace_callback(
            '/[-_\s]+(.)?/u',
            function ($match) use ($encoding) {
                return $match[1] ? mb_strtoupper($match[1], $encoding) : '';
            },
            $stringy->str
        );
        $stringy->str = preg_replace_callback(
            '/[\d]+(.)?/u',
            function ($match) use ($encoding) {
                return mb_strtoupper($match[0], $encoding);
            },
            $camelCase
        );
        return $stringy;
    }
    public function upperCamelize()
    {
        return $this->camelize()->upperCaseFirst();
    }
    public function dasherize()
    {
        return $this->applyDelimiter('-');
    }
    public function underscored()
    {
        return $this->applyDelimiter('_');
    }
    protected function applyDelimiter($delimiter)
    {
        $regexEncoding = mb_regex_encoding();
        mb_regex_encoding($this->encoding);
        $str = mb_ereg_replace('\B([A-Z])', $delimiter .'\1', $this->trim());
        $str = mb_ereg_replace('[-_\s]+', $delimiter, $str);
        $str = mb_strtolower($str, $this->encoding);
        mb_regex_encoding($regexEncoding);
        return static::create($str, $this->encoding);
    }
    public function swapCase()
    {
        $stringy = static::create($this->str, $this->encoding);
        $encoding = $stringy->encoding;
        $stringy->str = preg_replace_callback(
            '/[\S]/u',
            function ($match) use ($encoding) {
                if ($match[0] == mb_strtoupper($match[0], $encoding)) {
                    return mb_strtolower($match[0], $encoding);
                } else {
                    return mb_strtoupper($match[0], $encoding);
                }
            },
            $stringy->str
        );
        return $stringy;
    }
    public function titleize($ignore = null)
    {
        $buffer = $this->trim();
        $encoding = $this->encoding;
        $buffer = preg_replace_callback(
            '/([\S]+)/u',
            function ($match) use ($encoding, $ignore) {
                if ($ignore && in_array($match[0], $ignore)) {
                    return $match[0];
                } else {
                    $stringy = new Stringy($match[0], $encoding);
                    return (string) $stringy->upperCaseFirst();
                }
            },
            $buffer
        );
        return new Stringy($buffer, $encoding);
    }
    public function humanize()
    {
        $str = str_replace(array('_id', '_'), array('', ' '), $this->str);
        return static::create($str, $this->encoding)->trim()->upperCaseFirst();
    }
    public function tidy()
    {
        $str = preg_replace(array(
            '/\x{2026}/u',
            '/[\x{201C}\x{201D}]/u',
            '/[\x{2018}\x{2019}]/u',
            '/[\x{2013}\x{2014}]/u',
        ), array(
            '...',
            '"',
            "'",
            '-',
        ), $this->str);
        return static::create($str, $this->encoding);
    }
    public function collapseWhitespace()
    {
        return $this->regexReplace('[[:space:]]+', ' ')->trim();
    }
    public function toAscii($removeUnsupported = true)
    {
        $str = $this->str;
        foreach ($this->charsArray() as $key => $value) {
            $str = str_replace($value, $key, $str);
        }
        if ($removeUnsupported) {
            $str = preg_replace('/[^\x20-\x7E]/u', '', $str);
        }
        return static::create($str, $this->encoding);
    }
    protected function charsArray()
    {
        static $charsArray;
        if (isset($charsArray)) return $charsArray;
        return $charsArray = array(
            'a'    => array(
                            'à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ',
                            'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ä', 'ā', 'ą',
                            'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ',
                            'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ',
                            'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ'),
            'b'    => array('б', 'β', 'Ъ', 'Ь', 'ب'),
            'c'    => array('ç', 'ć', 'č', 'ĉ', 'ċ'),
            'd'    => array('ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ',
                            'д', 'δ', 'د', 'ض'),
            'e'    => array('é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ',
                            'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ',
                            'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э',
                            'є', 'ə'),
            'f'    => array('ф', 'φ', 'ف'),
            'g'    => array('ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ج'),
            'h'    => array('ĥ', 'ħ', 'η', 'ή', 'ح', 'ه'),
            'i'    => array('í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į',
                            'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ',
                            'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ',
                            'ῗ', 'і', 'ї', 'и'),
            'j'    => array('ĵ', 'ј', 'Ј'),
            'k'    => array('ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك'),
            'l'    => array('ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل'),
            'm'    => array('м', 'μ', 'م'),
            'n'    => array('ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن'),
            'o'    => array('ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ',
                            'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő',
                            'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό',
                            'ö', 'о', 'و', 'θ'),
            'p'    => array('п', 'π'),
            'r'    => array('ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر'),
            's'    => array('ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص'),
            't'    => array('ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط'),
            'u'    => array('ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ',
                            'ự', 'ü', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у'),
            'v'    => array('в'),
            'w'    => array('ŵ', 'ω', 'ώ'),
            'x'    => array('χ'),
            'y'    => array('ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ',
                            'ϋ', 'ύ', 'ΰ', 'ي'),
            'z'    => array('ź', 'ž', 'ż', 'з', 'ζ', 'ز'),
            'aa'   => array('ع'),
            'ae'   => array('æ'),
            'ch'   => array('ч'),
            'dj'   => array('ђ', 'đ'),
            'dz'   => array('џ'),
            'gh'   => array('غ'),
            'kh'   => array('х', 'خ'),
            'lj'   => array('љ'),
            'nj'   => array('њ'),
            'oe'   => array('œ'),
            'ps'   => array('ψ'),
            'sh'   => array('ш'),
            'shch' => array('щ'),
            'ss'   => array('ß'),
            'th'   => array('þ', 'ث', 'ذ', 'ظ'),
            'ts'   => array('ц'),
            'ya'   => array('я'),
            'yu'   => array('ю'),
            'zh'   => array('ж'),
            '(c)'  => array('©'),
            'A'    => array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ',
                            'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Ä', 'Å', 'Ā',
                            'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ',
                            'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ',
                            'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А'),
            'B'    => array('Б', 'Β'),
            'C'    => array('Ć', 'Č', 'Ĉ', 'Ċ'),
            'D'    => array('Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'),
            'E'    => array('É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ',
                            'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ',
                            'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э',
                            'Є', 'Ə'),
            'F'    => array('Ф', 'Φ'),
            'G'    => array('Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'),
            'H'    => array('Η', 'Ή'),
            'I'    => array('Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į',
                            'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ',
                            'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї'),
            'K'    => array('К', 'Κ'),
            'L'    => array('Ĺ', 'Ł', 'Л', 'Λ', 'Ļ'),
            'M'    => array('М', 'Μ'),
            'N'    => array('Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'),
            'O'    => array('Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ',
                            'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ö', 'Ø', 'Ō',
                            'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ',
                            'Ὸ', 'Ό', 'О', 'Θ', 'Ө'),
            'P'    => array('П', 'Π'),
            'R'    => array('Ř', 'Ŕ', 'Р', 'Ρ'),
            'S'    => array('Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'),
            'T'    => array('Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'),
            'U'    => array('Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ',
                            'Ự', 'Û', 'Ü', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У'),
            'V'    => array('В'),
            'W'    => array('Ω', 'Ώ'),
            'X'    => array('Χ'),
            'Y'    => array('Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ',
                            'Ы', 'Й', 'Υ', 'Ϋ'),
            'Z'    => array('Ź', 'Ž', 'Ż', 'З', 'Ζ'),
            'AE'   => array('Æ'),
            'CH'   => array('Ч'),
            'DJ'   => array('Ђ'),
            'DZ'   => array('Џ'),
            'KH'   => array('Х'),
            'LJ'   => array('Љ'),
            'NJ'   => array('Њ'),
            'PS'   => array('Ψ'),
            'SH'   => array('Ш'),
            'SHCH' => array('Щ'),
            'SS'   => array('ẞ'),
            'TH'   => array('Þ'),
            'TS'   => array('Ц'),
            'YA'   => array('Я'),
            'YU'   => array('Ю'),
            'ZH'   => array('Ж'),
            ' '    => array("\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81",
                            "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84",
                            "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87",
                            "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A",
                            "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"),
        );
    }
    public function pad($length, $padStr = ' ', $padType = 'right')
    {
        if (!in_array($padType, array('left', 'right', 'both'))) {
            throw new \InvalidArgumentException('Pad expects $padType ' .
                "to be one of 'left', 'right' or 'both'");
        }
        switch ($padType) {
            case 'left':
                return $this->padLeft($length, $padStr);
            case 'right':
                return $this->padRight($length, $padStr);
            default:
                return $this->padBoth($length, $padStr);
        }
    }
    public function padLeft($length, $padStr = ' ')
    {
        return $this->applyPadding($length - $this->length(), 0, $padStr);
    }
    public function padRight($length, $padStr = ' ')
    {
        return $this->applyPadding(0, $length - $this->length(), $padStr);
    }
    public function padBoth($length, $padStr = ' ')
    {
        $padding = $length - $this->length();
        return $this->applyPadding(floor($padding / 2), ceil($padding / 2),
            $padStr);
    }
    private function applyPadding($left = 0, $right = 0, $padStr = ' ')
    {
        $stringy = static::create($this->str, $this->encoding);
        $length = mb_strlen($padStr, $stringy->encoding);
        $strLength = $stringy->length();
        $paddedLength = $strLength + $left + $right;
        if (!$length || $paddedLength <= $strLength) {
            return $stringy;
        }
        $leftPadding = mb_substr(str_repeat($padStr, ceil($left / $length)), 0,
            $left, $stringy->encoding);
        $rightPadding = mb_substr(str_repeat($padStr, ceil($right / $length)),
            0, $right, $stringy->encoding);
        $stringy->str = $leftPadding . $stringy->str . $rightPadding;
        return $stringy;
    }
    public function startsWith($substring, $caseSensitive = true)
    {
        $substringLength = mb_strlen($substring, $this->encoding);
        $startOfStr = mb_substr($this->str, 0, $substringLength,
            $this->encoding);
        if (!$caseSensitive) {
            $substring = mb_strtolower($substring, $this->encoding);
            $startOfStr = mb_strtolower($startOfStr, $this->encoding);
        }
        return (string) $substring === $startOfStr;
    }
    public function endsWith($substring, $caseSensitive = true)
    {
        $substringLength = mb_strlen($substring, $this->encoding);
        $strLength = $this->length();
        $endOfStr = mb_substr($this->str, $strLength - $substringLength,
            $substringLength, $this->encoding);
        if (!$caseSensitive) {
            $substring = mb_strtolower($substring, $this->encoding);
            $endOfStr = mb_strtolower($endOfStr, $this->encoding);
        }
        return (string) $substring === $endOfStr;
    }
    public function toSpaces($tabLength = 4)
    {
        $spaces = str_repeat(' ', $tabLength);
        $str = str_replace("\t", $spaces, $this->str);
        return static::create($str, $this->encoding);
    }
    public function toTabs($tabLength = 4)
    {
        $spaces = str_repeat(' ', $tabLength);
        $str = str_replace($spaces, "\t", $this->str);
        return static::create($str, $this->encoding);
    }
    public function toTitleCase()
    {
        $str = mb_convert_case($this->str, MB_CASE_TITLE, $this->encoding);
        return static::create($str, $this->encoding);
    }
    public function toLowerCase()
    {
        $str = mb_strtolower($this->str, $this->encoding);
        return static::create($str, $this->encoding);
    }
    public function toUpperCase()
    {
        $str = mb_strtoupper($this->str, $this->encoding);
        return static::create($str, $this->encoding);
    }
    public function slugify($replacement = '-')
    {
        $stringy = $this->toAscii();
        $quotedReplacement = preg_quote($replacement);
        $pattern = "/[^a-zA-Z\d\s-_$quotedReplacement]/u";
        $stringy->str = preg_replace($pattern, '', $stringy);
        return $stringy->toLowerCase()->applyDelimiter($replacement)
                       ->removeLeft($replacement)->removeRight($replacement);
    }
    public function contains($needle, $caseSensitive = true)
    {
        $encoding = $this->encoding;
        if ($caseSensitive) {
            return (mb_strpos($this->str, $needle, 0, $encoding) !== false);
        } else {
            return (mb_stripos($this->str, $needle, 0, $encoding) !== false);
        }
    }
    public function containsAny($needles, $caseSensitive = true)
    {
        if (empty($needles)) {
            return false;
        }
        foreach ($needles as $needle) {
            if ($this->contains($needle, $caseSensitive)) {
                return true;
            }
        }
        return false;
    }
    public function containsAll($needles, $caseSensitive = true)
    {
        if (empty($needles)) {
            return false;
        }
        foreach ($needles as $needle) {
            if (!$this->contains($needle, $caseSensitive)) {
                return false;
            }
        }
        return true;
    }
    public function surround($substring)
    {
        $str = implode('', array($substring, $this->str, $substring));
        return static::create($str, $this->encoding);
    }
    public function insert($substring, $index)
    {
        $stringy = static::create($this->str, $this->encoding);
        if ($index > $stringy->length()) {
            return $stringy;
        }
        $start = mb_substr($stringy->str, 0, $index, $stringy->encoding);
        $end = mb_substr($stringy->str, $index, $stringy->length(),
            $stringy->encoding);
        $stringy->str = $start . $substring . $end;
        return $stringy;
    }
    public function truncate($length, $substring = '')
    {
        $stringy = static::create($this->str, $this->encoding);
        if ($length >= $stringy->length()) {
            return $stringy;
        }
        $substringLength = mb_strlen($substring, $stringy->encoding);
        $length = $length - $substringLength;
        $truncated = mb_substr($stringy->str, 0, $length, $stringy->encoding);
        $stringy->str = $truncated . $substring;
        return $stringy;
    }
    public function safeTruncate($length, $substring = '')
    {
        $stringy = static::create($this->str, $this->encoding);
        if ($length >= $stringy->length()) {
            return $stringy;
        }
        $encoding = $stringy->encoding;
        $substringLength = mb_strlen($substring, $encoding);
        $length = $length - $substringLength;
        $truncated = mb_substr($stringy->str, 0, $length, $encoding);
        if (mb_strpos($stringy->str, ' ', $length - 1, $encoding) != $length) {
            $lastPos = mb_strrpos($truncated, ' ', 0, $encoding);
            $truncated = mb_substr($truncated, 0, $lastPos, $encoding);
        }
        $stringy->str = $truncated . $substring;
        return $stringy;
    }
    public function reverse()
    {
        $strLength = $this->length();
        $reversed = '';
        for ($i = $strLength - 1; $i >= 0; $i--) {
            $reversed .= mb_substr($this->str, $i, 1, $this->encoding);
        }
        return static::create($reversed, $this->encoding);
    }
    public function shuffle()
    {
        $indexes = range(0, $this->length() - 1);
        shuffle($indexes);
        $shuffledStr = '';
        foreach ($indexes as $i) {
            $shuffledStr .= mb_substr($this->str, $i, 1, $this->encoding);
        }
        return static::create($shuffledStr, $this->encoding);
    }
    public function trim()
    {
        return static::create(trim($this->str), $this->encoding);
    }
    public function longestCommonPrefix($otherStr)
    {
        $encoding = $this->encoding;
        $maxLength = min($this->length(), mb_strlen($otherStr, $encoding));
        $longestCommonPrefix = '';
        for ($i = 0; $i < $maxLength; $i++) {
            $char = mb_substr($this->str, $i, 1, $encoding);
            if ($char == mb_substr($otherStr, $i, 1, $encoding)) {
                $longestCommonPrefix .= $char;
            } else {
                break;
            }
        }
        return static::create($longestCommonPrefix, $encoding);
    }
    public function longestCommonSuffix($otherStr)
    {
        $encoding = $this->encoding;
        $maxLength = min($this->length(), mb_strlen($otherStr, $encoding));
        $longestCommonSuffix = '';
        for ($i = 1; $i <= $maxLength; $i++) {
            $char = mb_substr($this->str, -$i, 1, $encoding);
            if ($char == mb_substr($otherStr, -$i, 1, $encoding)) {
                $longestCommonSuffix = $char . $longestCommonSuffix;
            } else {
                break;
            }
        }
        return static::create($longestCommonSuffix, $encoding);
    }
    public function longestCommonSubstring($otherStr)
    {
        $encoding = $this->encoding;
        $stringy = static::create($this->str, $encoding);
        $strLength = $stringy->length();
        $otherLength = mb_strlen($otherStr, $encoding);
        if ($strLength == 0 || $otherLength == 0) {
            $stringy->str = '';
            return $stringy;
        }
        $len = 0;
        $end = 0;
        $table = array_fill(0, $strLength + 1,
            array_fill(0, $otherLength + 1, 0));
        for ($i = 1; $i <= $strLength; $i++) {
            for ($j = 1; $j <= $otherLength; $j++) {
                $strChar = mb_substr($stringy->str, $i - 1, 1, $encoding);
                $otherChar = mb_substr($otherStr, $j - 1, 1, $encoding);
                if ($strChar == $otherChar) {
                    $table[$i][$j] = $table[$i - 1][$j - 1] + 1;
                    if ($table[$i][$j] > $len) {
                        $len = $table[$i][$j];
                        $end = $i;
                    }
                } else {
                    $table[$i][$j] = 0;
                }
            }
        }
        $stringy->str = mb_substr($stringy->str, $end - $len, $len, $encoding);
        return $stringy;
    }
    public function length()
    {
        return mb_strlen($this->str, $this->encoding);
    }
    public function substr($start, $length = null)
    {
        $length = $length === null ? $this->length() : $length;
        $str = mb_substr($this->str, $start, $length, $this->encoding);
        return static::create($str, $this->encoding);
    }
    public function at($index)
    {
        return $this->substr($index, 1);
    }
    public function first($n)
    {
        $stringy = static::create($this->str, $this->encoding);
        if ($n < 0) {
            $stringy->str = '';
        } else {
            return $stringy->substr(0, $n);
        }
        return $stringy;
    }
    public function last($n)
    {
        $stringy = static::create($this->str, $this->encoding);
        if ($n <= 0) {
            $stringy->str = '';
        } else {
            return $stringy->substr(-$n);
        }
        return $stringy;
    }
    public function ensureLeft($substring)
    {
        $stringy = static::create($this->str, $this->encoding);
        if (!$stringy->startsWith($substring)) {
            $stringy->str = $substring . $stringy->str;
        }
        return $stringy;
    }
    public function ensureRight($substring)
    {
        $stringy = static::create($this->str, $this->encoding);
        if (!$stringy->endsWith($substring)) {
            $stringy->str .= $substring;
        }
        return $stringy;
    }
    public function removeLeft($substring)
    {
        $stringy = static::create($this->str, $this->encoding);
        if ($stringy->startsWith($substring)) {
            $substringLength = mb_strlen($substring, $stringy->encoding);
            return $stringy->substr($substringLength);
        }
        return $stringy;
    }
    public function removeRight($substring)
    {
        $stringy = static::create($this->str, $this->encoding);
        if ($stringy->endsWith($substring)) {
            $substringLength = mb_strlen($substring, $stringy->encoding);
            return $stringy->substr(0, $stringy->length() - $substringLength);
        }
        return $stringy;
    }
    private function matchesPattern($pattern)
    {
        $regexEncoding = mb_regex_encoding();
        mb_regex_encoding($this->encoding);
        $match = mb_ereg_match($pattern, $this->str);
        mb_regex_encoding($regexEncoding);
        return $match;
    }
    public function hasLowerCase()
    {
        return $this->matchesPattern('.*[[:lower:]]');
    }
    public function hasUpperCase()
    {
        return $this->matchesPattern('.*[[:upper:]]');
    }
    public function isAlpha()
    {
        return $this->matchesPattern('^[[:alpha:]]*$');
    }
    public function isAlphanumeric()
    {
        return $this->matchesPattern('^[[:alnum:]]*$');
    }
    public function isHexadecimal()
    {
        return $this->matchesPattern('^[[:xdigit:]]*$');
    }
    public function isBlank()
    {
        return $this->matchesPattern('^[[:space:]]*$');
    }
    public function isJson()
    {
        json_decode($this->str);
        return (json_last_error() === JSON_ERROR_NONE);
    }
    public function isLowerCase()
    {
        return $this->matchesPattern('^[[:lower:]]*$');
    }
    public function isUpperCase()
    {
        return $this->matchesPattern('^[[:upper:]]*$');
    }
    public function isSerialized()
    {
        return $this->str === 'b:0;' || @unserialize($this->str) !== false;
    }
    public function countSubstr($substring, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return mb_substr_count($this->str, $substring, $this->encoding);
        }
        $str = mb_strtoupper($this->str, $this->encoding);
        $substring = mb_strtoupper($substring, $this->encoding);
        return mb_substr_count($str, $substring, $this->encoding);
    }
    public function replace($search, $replacement)
    {
        return $this->regexReplace(preg_quote($search), $replacement);
    }
    public function regexReplace($pattern, $replacement, $options = 'msr')
    {
        $regexEncoding = mb_regex_encoding();
        mb_regex_encoding($this->encoding);
        $str = mb_ereg_replace($pattern, $replacement, $this->str, $options);
        mb_regex_encoding($regexEncoding);
        return static::create($str, $this->encoding);
    }
}
