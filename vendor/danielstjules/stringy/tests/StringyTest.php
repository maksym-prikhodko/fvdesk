<?php
require __DIR__ . '/../src/Stringy.php';
use Stringy\Stringy as S;
class StringyTestCase extends CommonTest
{
    public function testConstruct()
    {
        $stringy = new S('foo bar', 'UTF-8');
        $this->assertStringy($stringy);
        $this->assertEquals('foo bar', (string) $stringy);
        $this->assertEquals('UTF-8', $stringy->getEncoding());
    }
    public function testConstructWithArray()
    {
        (string) new S(array());
        $this->fail('Expecting exception when the constructor is passed an array');
    }
    public function testMissingToString()
    {
        (string) new S(new stdClass());
        $this->fail('Expecting exception when the constructor is passed an ' .
                    'object without a __toString method');
    }
    public function testToString($expected, $str)
    {
        $this->assertEquals($expected, (string) new S($str));
    }
    public function toStringProvider()
    {
        return array(
            array('', null),
            array('', false),
            array('1', true),
            array('-9', -9),
            array('1.18', 1.18),
            array(' string  ', ' string  ')
        );
    }
    public function testCreate()
    {
        $stringy = S::create('foo bar', 'UTF-8');
        $this->assertStringy($stringy);
        $this->assertEquals('foo bar', (string) $stringy);
        $this->assertEquals('UTF-8', $stringy->getEncoding());
    }
    public function testChaining()
    {
        $stringy = S::create("Fòô     Bàř", 'UTF-8');
        $this->assertStringy($stringy);
        $result = $stringy->collapseWhitespace()->swapCase()->upperCaseFirst();
        $this->assertEquals('FÒÔ bÀŘ', $result);
    }
    public function testCount()
    {
        $stringy = S::create('Fòô', 'UTF-8');
        $this->assertEquals(3, $stringy->count());
        $this->assertEquals(3, count($stringy));
    }
    public function testGetIterator()
    {
        $stringy = S::create('Fòô Bàř', 'UTF-8');
        $valResult = array();
        foreach ($stringy as $char) {
            $valResult[] = $char;
        }
        $keyValResult = array();
        foreach ($stringy as $pos => $char) {
            $keyValResult[$pos] = $char;
        }
        $this->assertEquals(array('F', 'ò', 'ô', ' ', 'B', 'à', 'ř'), $valResult);
        $this->assertEquals(array('F', 'ò', 'ô', ' ', 'B', 'à', 'ř'), $keyValResult);
    }
    public function testOffsetExists($expected, $offset)
    {
        $stringy = S::create('fòô', 'UTF-8');
        $this->assertEquals($expected, $stringy->offsetExists($offset));
        $this->assertEquals($expected, isset($stringy[$offset]));
    }
    public function offsetExistsProvider()
    {
        return array(
            array(true, 0),
            array(true, 2),
            array(false, 3),
            array(true, -1),
            array(true, -3),
            array(false, -4)
        );
    }
    public function testOffsetGet()
    {
        $stringy = S::create('fòô', 'UTF-8');
        $this->assertEquals('f', $stringy->offsetGet(0));
        $this->assertEquals('ô', $stringy->offsetGet(2));
        $this->assertEquals('ô', $stringy[2]);
    }
    public function testOffsetGetOutOfBounds()
    {
        $stringy = S::create('fòô', 'UTF-8');
        $test = $stringy[3];
    }
    public function testOffsetSet()
    {
        $stringy = S::create('fòô', 'UTF-8');
        $stringy[1] = 'invalid';
    }
    public function testOffsetUnset()
    {
        $stringy = S::create('fòô', 'UTF-8');
        unset($stringy[1]);
    }
    public function testChars($expected, $str, $encoding = null)
    {
        $result = S::create($str, $encoding)->chars();
        $this->assertInternalType('array', $result);
        foreach ($result as $char) {
            $this->assertInternalType('string', $char);
        }
        $this->assertEquals($expected, $result);
    }
    public function testUpperCaseFirst($expected, $str, $encoding = null)
    {
        $result = S::create($str, $encoding)->upperCaseFirst();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
    }
    public function testLowerCaseFirst($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->lowerCaseFirst();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testCamelize($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->camelize();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testUpperCamelize($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->upperCamelize();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testDasherize($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->dasherize();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testUnderscored($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->underscored();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testSwapCase($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->swapCase();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testTitleize($expected, $str, $ignore = null,
                                 $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->titleize($ignore);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testHumanize($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->humanize();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testTidy($expected, $str)
    {
        $stringy = S::create($str);
        $result = $stringy->tidy();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testCollapseWhitespace($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->collapseWhitespace();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testToAscii($expected, $str, $removeUnsupported = true)
    {
        $stringy = S::create($str);
        $result = $stringy->toAscii($removeUnsupported);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testPad($expected, $str, $length, $padStr = ' ',
                            $padType = 'right', $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->pad($length, $padStr, $padType);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testPadException()
    {
        $stringy = S::create('foo');
        $result = $stringy->pad(5, 'foo', 'bar');
    }
    public function testPadLeft($expected, $str, $length, $padStr = ' ',
                                $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->padLeft($length, $padStr);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testPadRight($expected, $str, $length, $padStr = ' ',
                                 $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->padRight($length, $padStr);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testPadBoth($expected, $str, $length, $padStr = ' ',
                                $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->padBoth($length, $padStr);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testStartsWith($expected, $str, $substring,
                                   $caseSensitive = true, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->startsWith($substring, $caseSensitive);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testEndsWith($expected, $str, $substring,
                                 $caseSensitive = true, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->endsWith($substring, $caseSensitive);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testToSpaces($expected, $str, $tabLength = 4)
    {
        $stringy = S::create($str);
        $result = $stringy->toSpaces($tabLength);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testToTabs($expected, $str, $tabLength = 4)
    {
        $stringy = S::create($str);
        $result = $stringy->toTabs($tabLength);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testToLowerCase($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->toLowerCase();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testToTitleCase($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->toTitleCase();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testToUpperCase($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->toUpperCase();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testSlugify($expected, $str, $replacement = '-')
    {
        $stringy = S::create($str);
        $result = $stringy->slugify($replacement);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testContains($expected, $haystack, $needle,
                                 $caseSensitive = true, $encoding = null)
    {
        $stringy = S::create($haystack, $encoding);
        $result = $stringy->contains($needle, $caseSensitive);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($haystack, $stringy);
    }
    public function testcontainsAny($expected, $haystack, $needles,
                                    $caseSensitive = true, $encoding = null)
    {
        $stringy = S::create($haystack, $encoding);
        $result = $stringy->containsAny($needles, $caseSensitive);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($haystack, $stringy);
    }
    public function testContainsAll($expected, $haystack, $needles,
                                    $caseSensitive = true, $encoding = null)
    {
        $stringy = S::create($haystack, $encoding);
        $result = $stringy->containsAll($needles, $caseSensitive);
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($haystack, $stringy);
    }
    public function testSurround($expected, $str, $substring)
    {
        $stringy = S::create($str);
        $result = $stringy->surround($substring);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testInsert($expected, $str, $substring, $index,
                               $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->insert($substring, $index);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testTruncate($expected, $str, $length, $substring = '',
                                 $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->truncate($length, $substring);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testSafeTruncate($expected, $str, $length, $substring = '',
                                     $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->safeTruncate($length, $substring);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testReverse($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->reverse();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testShuffle($str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $encoding = $encoding ?: mb_internal_encoding();
        $result = $stringy->shuffle();
        $this->assertStringy($result);
        $this->assertEquals($str, $stringy);
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
        $stringy = S::create($str);
        $result = $stringy->trim();
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testLongestCommonPrefix($expected, $str, $otherStr,
                                            $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->longestCommonPrefix($otherStr);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testLongestCommonSuffix($expected, $str, $otherStr,
                                            $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->longestCommonSuffix($otherStr);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testLongestCommonSubstring($expected, $str, $otherStr,
                                               $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->longestCommonSubstring($otherStr);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testLength($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->length();
        $this->assertInternalType('int', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testSubstr($expected, $str, $start, $length = null,
                               $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->substr($start, $length);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testAt($expected, $str, $index, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->at($index);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testFirst($expected, $str, $n, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->first($n);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testLast($expected, $str, $n, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->last($n);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testEnsureLeft($expected, $str, $substring, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->ensureLeft($substring);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testEnsureRight($expected, $str, $substring, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->ensureRight($substring);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testRemoveLeft($expected, $str, $substring, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->removeLeft($substring);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testRemoveRight($expected, $str, $substring, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->removeRight($substring);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testIsAlpha($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->isAlpha();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testIsAlphanumeric($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->isAlphanumeric();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testIsBlank($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->isBlank();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testIsJson($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->isJson();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testIsLowerCase($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->isLowerCase();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testHasLowerCase($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->hasLowerCase();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testIsSerialized($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->isSerialized();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testIsUpperCase($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->isUpperCase();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testHasUpperCase($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->hasUpperCase();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testIsHexadecimal($expected, $str, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->isHexadecimal();
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testCountSubstr($expected, $str, $substring,
                                    $caseSensitive = true, $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->countSubstr($substring, $caseSensitive);
        $this->assertInternalType('int', $result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testReplace($expected, $str, $search, $replacement,
                                $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->replace($search, $replacement);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
    public function testregexReplace($expected, $str, $pattern, $replacement,
                                     $options = 'msr', $encoding = null)
    {
        $stringy = S::create($str, $encoding);
        $result = $stringy->regexReplace($pattern, $replacement, $options);
        $this->assertStringy($result);
        $this->assertEquals($expected, $result);
        $this->assertEquals($str, $stringy);
    }
}
