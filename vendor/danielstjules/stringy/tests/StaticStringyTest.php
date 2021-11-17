<?php
require __DIR__ . '/../src/StaticStringy.php';
use Stringy\StaticStringy as S;
class StaticStringyTestCase extends CommonTest
{
    public function testChars($expected, $str, $encoding = null)
    {
        $result = S::chars($str, $encoding);
        $this->assertInternalType('array', $result);
        foreach ($result as $char) {
            $this->assertInternalType('string', $char);
        }
        $this->assertEquals($expected, $result);
    }
    public function testUpperCaseFirst($expected, $str, $encoding = null)
    {
        $result = S::upperCaseFirst($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testLowerCaseFirst($expected, $str, $encoding = null)
    {
        $result = S::lowerCaseFirst($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testCamelize($expected, $str, $encoding = null)
    {
        $result = S::camelize($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testUpperCamelize($expected, $str, $encoding = null)
    {
        $result = S::upperCamelize($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testDasherize($expected, $str, $encoding = null)
    {
        $result = S::dasherize($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testUnderscored($expected, $str, $encoding = null)
    {
        $result = S::underscored($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testSwapCase($expected, $str, $encoding = null)
    {
        $result = S::swapCase($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testTitleize($expected, $str, $ignore = null,
                                 $encoding = null)
    {
        $result = S::titleize($str, $ignore, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testHumanize($expected, $str, $encoding = null)
    {
        $result = S::humanize($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testTidy($expected, $str)
    {
        $result = S::tidy($str);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testCollapseWhitespace($expected, $str, $encoding = null)
    {
        $result = S::collapseWhitespace($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testToAscii($expected, $str, $removeUnsupported = true)
    {
        $result = S::toAscii($str, $removeUnsupported);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testPad($expected, $str, $length, $padStr = ' ',
                            $padType = 'right', $encoding = null)
    {
        $result = S::pad($str, $length, $padStr, $padType, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testPadException()
    {
        $result = S::pad('string', 5, 'foo', 'bar');
    }
    public function testPadLeft($expected, $str, $length, $padStr = ' ',
                                $encoding = null)
    {
        $result = S::padLeft($str, $length, $padStr, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testPadRight($expected, $str, $length, $padStr = ' ',
                                 $encoding = null)
    {
        $result = S::padRight($str, $length, $padStr, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testPadBoth($expected, $str, $length, $padStr = ' ',
                                $encoding = null)
    {
        $result = S::padBoth($str, $length, $padStr, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testStartsWith($expected, $str, $substring,
                                   $caseSensitive = true, $encoding = null)
    {
        $result = S::startsWith($str, $substring, $caseSensitive, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testEndsWith($expected, $str, $substring,
                                 $caseSensitive = true, $encoding = null)
    {
        $result = S::endsWith($str, $substring, $caseSensitive, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testToSpaces($expected, $str, $tabLength = 4)
    {
        $result = S::toSpaces($str, $tabLength);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testToTabs($expected, $str, $tabLength = 4)
    {
        $result = S::toTabs($str, $tabLength);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testToLowerCase($expected, $str, $encoding = null)
    {
        $result = S::toLowerCase($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testToTitleCase($expected, $str, $encoding = null)
    {
        $result = S::toTitleCase($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testToUpperCase($expected, $str, $encoding = null)
    {
        $result = S::toUpperCase($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testSlugify($expected, $str, $replacement = '-')
    {
        $result = S::slugify($str, $replacement);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testContains($expected, $haystack, $needle,
                                 $caseSensitive = true, $encoding = null)
    {
        $result = S::contains($haystack, $needle, $caseSensitive, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testcontainsAny($expected, $haystack, $needles,
                                    $caseSensitive = true, $encoding = null)
    {
        $result = S::containsAny($haystack, $needles, $caseSensitive, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testContainsAll($expected, $haystack, $needles,
                                    $caseSensitive = true, $encoding = null)
    {
        $result = S::containsAll($haystack, $needles, $caseSensitive, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testSurround($expected, $str, $substring)
    {
        $result = S::surround($str, $substring);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testInsert($expected, $str, $substring, $index,
                               $encoding = null)
    {
        $result = S::insert($str, $substring, $index, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testTruncate($expected, $str, $length, $substring = '',
                                 $encoding = null)
    {
        $result = S::truncate($str, $length, $substring, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testSafeTruncate($expected, $str, $length, $substring = '',
                                     $encoding = null)
    {
        $result = S::safeTruncate($str, $length, $substring, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testReverse($expected, $str, $encoding = null)
    {
        $result = S::reverse($str, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testShuffle($str, $encoding = null)
    {
        $result = S::shuffle($str, $encoding);
        $encoding = $encoding ?: mb_internal_encoding();
        $this->assertInternalType('string', $result);
        $this->assertEquals(mb_strlen($str, $encoding),
            mb_strlen($result, $encoding));
        for ($i = 0; $i < mb_strlen($str, $encoding); $i++) {
            $char = mb_substr($str, $i, 1, $encoding);
            $countBefore = mb_substr_count($str, $char, $encoding);
            $countAfter = mb_substr_count($result, $char, $encoding);
            $this->assertEquals($countBefore, $countAfter);
        }
    }
    public function testTrim($expected, $str)
    {
        $result = S::trim($str);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testLongestCommonPrefix($expected, $str, $otherStr,
                                            $encoding = null)
    {
        $result = S::longestCommonPrefix($str, $otherStr, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testLongestCommonSuffix($expected, $str, $otherStr,
                                            $encoding = null)
    {
        $result = S::longestCommonSuffix($str, $otherStr, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testLongestCommonSubstring($expected, $str, $otherStr,
                                               $encoding = null)
    {
        $result = S::longestCommonSubstring($str, $otherStr, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testLength($expected, $str, $encoding = null)
    {
        $result = S::length($str, $encoding);
        $this->assertEquals($expected, $result);
        $this->assertInternalType('int', $result);
    }
    public function testSubstr($expected, $str, $start, $length = null,
                               $encoding = null)
    {
        $result = S::substr($str, $start, $length, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testAt($expected, $str, $index, $encoding = null)
    {
        $result = S::at($str, $index, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testFirst($expected, $str, $n, $encoding = null)
    {
        $result = S::first($str, $n, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testLast($expected, $str, $n, $encoding = null)
    {
        $result = S::last($str, $n, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testEnsureLeft($expected, $str, $substring, $encoding = null)
    {
        $result = S::ensureLeft($str, $substring, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testEnsureRight($expected, $str, $substring, $encoding = null)
    {
        $result = S::ensureRight($str, $substring, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testRemoveLeft($expected, $str, $substring, $encoding = null)
    {
        $result = S::removeLeft($str, $substring, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testRemoveRight($expected, $str, $substring, $encoding = null)
    {
        $result = S::removeRight($str, $substring, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testIsAlpha($expected, $str, $encoding = null)
    {
        $result = S::isAlpha($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testIsAlphanumeric($expected, $str, $encoding = null)
    {
        $result = S::isAlphanumeric($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testIsBlank($expected, $str, $encoding = null)
    {
        $result = S::isBlank($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testIsJson($expected, $str, $encoding = null)
    {
        $result = S::isJson($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testIsLowerCase($expected, $str, $encoding = null)
    {
        $result = S::isLowerCase($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testHasLowerCase($expected, $str, $encoding = null)
    {
        $result = S::hasLowerCase($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testIsSerialized($expected, $str, $encoding = null)
    {
        $result = S::isSerialized($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testIsUpperCase($expected, $str, $encoding = null)
    {
        $result = S::isUpperCase($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testHasUpperCase($expected, $str, $encoding = null)
    {
        $result = S::hasUpperCase($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testIsHexadecimal($expected, $str, $encoding = null)
    {
        $result = S::isHexadecimal($str, $encoding);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }
    public function testCountSubstr($expected, $str, $substring,
                                    $caseSensitive = true, $encoding = null)
    {
        $result = S::countSubstr($str, $substring, $caseSensitive, $encoding);
        $this->assertInternalType('int', $result);
        $this->assertEquals($expected, $result);
    }
    public function testReplace($expected, $str, $search, $replacement,
                                $encoding = null)
    {
        $result = S::replace($str, $search, $replacement, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
    public function testRegexReplace($expected, $str, $pattern, $replacement,
                                     $options = 'msr', $encoding = null)
    {
        $result = S::regexReplace($str, $pattern, $replacement, $options, $encoding);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
}
