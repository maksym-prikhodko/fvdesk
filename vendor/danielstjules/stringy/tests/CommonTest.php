<?php
use Stringy\Stringy;
abstract class CommonTest extends PHPUnit_Framework_TestCase
{
    public function assertStringy($actual)
    {
        $this->assertInstanceOf('Stringy\Stringy', $actual);
    }
    public function charsProvider()
    {
        return array(
            array(array(), ''),
            array(array('T', 'e', 's', 't'), 'Test'),
            array(array('F', 'ò', 'ô', ' ', 'B', 'à', 'ř'), 'Fòô Bàř', 'UTF-8')
        );
    }
    public function upperCaseFirstProvider()
    {
        return array(
            array('Test', 'Test'),
            array('Test', 'test'),
            array('1a', '1a'),
            array('Σ test', 'σ test', 'UTF-8'),
            array(' σ test', ' σ test', 'UTF-8')
        );
    }
    public function lowerCaseFirstProvider()
    {
        return array(
            array('test', 'Test'),
            array('test', 'test'),
            array('1a', '1a'),
            array('σ test', 'Σ test', 'UTF-8'),
            array(' Σ test', ' Σ test', 'UTF-8')
        );
    }
    public function camelizeProvider()
    {
        return array(
            array('camelCase', 'CamelCase'),
            array('camelCase', 'Camel-Case'),
            array('camelCase', 'camel case'),
            array('camelCase', 'camel -case'),
            array('camelCase', 'camel - case'),
            array('camelCase', 'camel_case'),
            array('camelCTest', 'camel c test'),
            array('stringWith1Number', 'string_with1number'),
            array('stringWith22Numbers', 'string-with-2-2 numbers'),
            array('1Camel2Case', '1camel2case'),
            array('camelΣase', 'camel σase', 'UTF-8'),
            array('στανιλCase', 'Στανιλ case', 'UTF-8'),
            array('σamelCase', 'σamel  Case', 'UTF-8')
        );
    }
    public function upperCamelizeProvider()
    {
        return array(
            array('CamelCase', 'camelCase'),
            array('CamelCase', 'Camel-Case'),
            array('CamelCase', 'camel case'),
            array('CamelCase', 'camel -case'),
            array('CamelCase', 'camel - case'),
            array('CamelCase', 'camel_case'),
            array('CamelCTest', 'camel c test'),
            array('StringWith1Number', 'string_with1number'),
            array('StringWith22Numbers', 'string-with-2-2 numbers'),
            array('1Camel2Case', '1camel2case'),
            array('CamelΣase', 'camel σase', 'UTF-8'),
            array('ΣτανιλCase', 'στανιλ case', 'UTF-8'),
            array('ΣamelCase', 'Σamel  Case', 'UTF-8')
        );
    }
    public function dasherizeProvider()
    {
        return array(
            array('test-case', 'testCase'),
            array('test-case', 'Test-Case'),
            array('test-case', 'test case'),
            array('-test-case', '-test -case'),
            array('test-case', 'test - case'),
            array('test-case', 'test_case'),
            array('test-c-test', 'test c test'),
            array('test-d-case', 'TestDCase'),
            array('test-c-c-test', 'TestCCTest'),
            array('string-with1number', 'string_with1number'),
            array('string-with-2-2-numbers', 'String-with_2_2 numbers'),
            array('1test2case', '1test2case'),
            array('dash-σase', 'dash Σase', 'UTF-8'),
            array('στανιλ-case', 'Στανιλ case', 'UTF-8'),
            array('σash-case', 'Σash  Case', 'UTF-8')
        );
    }
    public function underscoredProvider()
    {
        return array(
            array('test_case', 'testCase'),
            array('test_case', 'Test-Case'),
            array('test_case', 'test case'),
            array('test_case', 'test -case'),
            array('_test_case', '-test - case'),
            array('test_case', 'test_case'),
            array('test_c_test', '  test c test'),
            array('test_u_case', 'TestUCase'),
            array('test_c_c_test', 'TestCCTest'),
            array('string_with1number', 'string_with1number'),
            array('string_with_2_2_numbers', 'String-with_2_2 numbers'),
            array('1test2case', '1test2case'),
            array('test_σase', 'test Σase', 'UTF-8'),
            array('στανιλ_case', 'Στανιλ case', 'UTF-8'),
            array('σash_case', 'Σash  Case', 'UTF-8')
        );
    }
    public function swapCaseProvider()
    {
        return array(
            array('TESTcASE', 'testCase'),
            array('tEST-cASE', 'Test-Case'),
            array(' - σASH  cASE', ' - Σash  Case', 'UTF-8'),
            array('νΤΑΝΙΛ', 'Ντανιλ', 'UTF-8')
        );
    }
    public function titleizeProvider()
    {
        $ignore = array('at', 'by', 'for', 'in', 'of', 'on', 'out', 'to', 'the');
        return array(
            array('Testing The Method', 'testing the method'),
            array('Testing the Method', 'testing the method', $ignore, 'UTF-8'),
            array('I Like to Watch DVDs at Home', 'i like to watch DVDs at home',
                $ignore, 'UTF-8'),
            array('Θα Ήθελα Να Φύγει', '  Θα ήθελα να φύγει  ', null, 'UTF-8')
        );
    }
    public function humanizeProvider()
    {
        return array(
            array('Author', 'author_id'),
            array('Test user', ' _test_user_'),
            array('Συγγραφέας', ' συγγραφέας_id ', 'UTF-8')
        );
    }
    public function tidyProvider()
    {
        return array(
            array('"I see..."', '“I see…”'),
            array("'This too'", "‘This too’"),
            array('test-dash', 'test—dash'),
            array('Ο συγγραφέας είπε...', 'Ο συγγραφέας είπε…')
        );
    }
    public function collapseWhitespaceProvider()
    {
        return array(
            array('foo bar', '  foo   bar  '),
            array('test string', 'test string'),
            array('Ο συγγραφέας', '   Ο     συγγραφέας  '),
            array('123', ' 123 '),
            array('', ' ', 'UTF-8'), 
            array('', '           ', 'UTF-8'), 
            array('', ' ', 'UTF-8'), 
            array('', ' ', 'UTF-8'), 
            array('', '　', 'UTF-8'), 
            array('1 2 3', '  1  2  3　　', 'UTF-8'),
            array('', ' '),
            array('', ''),
        );
    }
    public function toAsciiProvider()
    {
        return array(
            array('foo bar', 'fòô bàř'),
            array(' TEST ', ' ŤÉŚŢ '),
            array('f = z = 3', 'φ = ź = 3'),
            array('perevirka', 'перевірка'),
            array('lysaya gora', 'лысая гора'),
            array('shchuka', 'щука'),
            array('', '漢字'),
            array('xin chao the gioi', 'xin chào thế giới'),
            array('XIN CHAO THE GIOI', 'XIN CHÀO THẾ GIỚI'),
            array('dam phat chet luon', 'đấm phát chết luôn'),
            array(' ', ' '), 
            array('           ', '           '), 
            array(' ', ' '), 
            array(' ', ' '), 
            array(' ', '　'), 
            array('', '𐍉'), 
            array('𐍉', '𐍉', false),
        );
    }
    public function padProvider()
    {
        return array(
            array('foo bar', 'foo bar', -1),
            array('foo bar', 'foo bar', 7),
            array('fòô bàř', 'fòô bàř', 7, ' ', 'right', 'UTF-8'),
            array('foo bar  ', 'foo bar', 9),
            array('foo bar_*', 'foo bar', 9, '_*', 'right'),
            array('fòô bàř¬ø¬', 'fòô bàř', 10, '¬ø', 'right', 'UTF-8'),
            array('  foo bar', 'foo bar', 9, ' ', 'left'),
            array('_*foo bar', 'foo bar', 9, '_*', 'left'),
            array('¬ø¬fòô bàř', 'fòô bàř', 10, '¬ø', 'left', 'UTF-8'),
            array('foo bar ', 'foo bar', 8, ' ', 'both'),
            array('¬fòô bàř¬ø', 'fòô bàř', 10, '¬ø', 'both', 'UTF-8'),
            array('¬øfòô bàř¬øÿ', 'fòô bàř', 12, '¬øÿ', 'both', 'UTF-8')
        );
    }
    public function padLeftProvider()
    {
        return array(
            array('  foo bar', 'foo bar', 9),
            array('_*foo bar', 'foo bar', 9, '_*'),
            array('_*_foo bar', 'foo bar', 10, '_*'),
            array('  fòô bàř', 'fòô bàř', 9, ' ', 'UTF-8'),
            array('¬øfòô bàř', 'fòô bàř', 9, '¬ø', 'UTF-8'),
            array('¬ø¬fòô bàř', 'fòô bàř', 10, '¬ø', 'UTF-8'),
            array('¬ø¬øfòô bàř', 'fòô bàř', 11, '¬ø', 'UTF-8'),
        );
    }
    public function padRightProvider()
    {
        return array(
            array('foo bar  ', 'foo bar', 9),
            array('foo bar_*', 'foo bar', 9, '_*'),
            array('foo bar_*_', 'foo bar', 10, '_*'),
            array('fòô bàř  ', 'fòô bàř', 9, ' ', 'UTF-8'),
            array('fòô bàř¬ø', 'fòô bàř', 9, '¬ø', 'UTF-8'),
            array('fòô bàř¬ø¬', 'fòô bàř', 10, '¬ø', 'UTF-8'),
            array('fòô bàř¬ø¬ø', 'fòô bàř', 11, '¬ø', 'UTF-8'),
        );
    }
    public function padBothProvider()
    {
        return array(
            array('foo bar ', 'foo bar', 8),
            array(' foo bar ', 'foo bar', 9, ' '),
            array('fòô bàř ', 'fòô bàř', 8, ' ', 'UTF-8'),
            array(' fòô bàř ', 'fòô bàř', 9, ' ', 'UTF-8'),
            array('fòô bàř¬', 'fòô bàř', 8, '¬ø', 'UTF-8'),
            array('¬fòô bàř¬', 'fòô bàř', 9, '¬ø', 'UTF-8'),
            array('¬fòô bàř¬ø', 'fòô bàř', 10, '¬ø', 'UTF-8'),
            array('¬øfòô bàř¬ø', 'fòô bàř', 11, '¬ø', 'UTF-8'),
            array('¬fòô bàř¬ø', 'fòô bàř', 10, '¬øÿ', 'UTF-8'),
            array('¬øfòô bàř¬ø', 'fòô bàř', 11, '¬øÿ', 'UTF-8'),
            array('¬øfòô bàř¬øÿ', 'fòô bàř', 12, '¬øÿ', 'UTF-8')
        );
    }
    public function startsWithProvider()
    {
        return array(
            array(true, 'foo bars', 'foo bar'),
            array(true, 'FOO bars', 'foo bar', false),
            array(true, 'FOO bars', 'foo BAR', false),
            array(true, 'FÒÔ bàřs', 'fòô bàř', false, 'UTF-8'),
            array(true, 'fòô bàřs', 'fòô BÀŘ', false, 'UTF-8'),
            array(false, 'foo bar', 'bar'),
            array(false, 'foo bar', 'foo bars'),
            array(false, 'FOO bar', 'foo bars'),
            array(false, 'FOO bars', 'foo BAR'),
            array(false, 'FÒÔ bàřs', 'fòô bàř', true, 'UTF-8'),
            array(false, 'fòô bàřs', 'fòô BÀŘ', true, 'UTF-8'),
        );
    }
    public function endsWithProvider()
    {
        return array(
            array(true, 'foo bars', 'o bars'),
            array(true, 'FOO bars', 'o bars', false),
            array(true, 'FOO bars', 'o BARs', false),
            array(true, 'FÒÔ bàřs', 'ô bàřs', false, 'UTF-8'),
            array(true, 'fòô bàřs', 'ô BÀŘs', false, 'UTF-8'),
            array(false, 'foo bar', 'foo'),
            array(false, 'foo bar', 'foo bars'),
            array(false, 'FOO bar', 'foo bars'),
            array(false, 'FOO bars', 'foo BARS'),
            array(false, 'FÒÔ bàřs', 'fòô bàřs', true, 'UTF-8'),
            array(false, 'fòô bàřs', 'fòô BÀŘS', true, 'UTF-8'),
        );
    }
    public function toSpacesProvider()
    {
        return array(
            array('    foo    bar    ', '	foo	bar	'),
            array('     foo     bar     ', '	foo	bar	', 5),
            array('    foo  bar  ', '		foo	bar	', 2),
            array('foobar', '	foo	bar	', 0),
            array("    foo\n    bar", "	foo\n	bar"),
            array("    fòô\n    bàř", "	fòô\n	bàř")
        );
    }
    public function toTabsProvider()
    {
        return array(
            array('	foo	bar	', '    foo    bar    '),
            array('	foo	bar	', '     foo     bar     ', 5),
            array('		foo	bar	', '    foo  bar  ', 2),
            array("	foo\n	bar", "    foo\n    bar"),
            array("	fòô\n	bàř", "    fòô\n    bàř")
        );
    }
    public function toLowerCaseProvider()
    {
        return array(
            array('foo bar', 'FOO BAR'),
            array(' foo_bar ', ' FOO_bar '),
            array('fòô bàř', 'FÒÔ BÀŘ', 'UTF-8'),
            array(' fòô_bàř ', ' FÒÔ_bàř ', 'UTF-8'),
            array('αυτοκίνητο', 'ΑΥΤΟΚΊΝΗΤΟ', 'UTF-8'),
        );
    }
    public function toTitleCaseProvider()
    {
        return array(
            array('Foo Bar', 'foo bar'),
            array(' Foo_Bar ', ' foo_bar '),
            array('Fòô Bàř', 'fòô bàř', 'UTF-8'),
            array(' Fòô_Bàř ', ' fòô_bàř ', 'UTF-8'),
            array('Αυτοκίνητο Αυτοκίνητο', 'αυτοκίνητο αυτοκίνητο', 'UTF-8'),
        );
    }
    public function toUpperCaseProvider()
    {
        return array(
            array('FOO BAR', 'foo bar'),
            array(' FOO_BAR ', ' FOO_bar '),
            array('FÒÔ BÀŘ', 'fòô bàř', 'UTF-8'),
            array(' FÒÔ_BÀŘ ', ' FÒÔ_bàř ', 'UTF-8'),
            array('ΑΥΤΟΚΊΝΗΤΟ', 'αυτοκίνητο', 'UTF-8'),
        );
    }
    public function slugifyProvider()
    {
        return array(
            array('foo-bar', ' foo  bar '),
            array('foo-bar', 'foo -.-"-...bar'),
            array('another-foo-bar', 'another..& foo -.-"-...bar'),
            array('foo-dbar', " Foo d'Bar "),
            array('a-string-with-dashes', 'A string-with-dashes'),
            array('using-strings-like-foo-bar', 'Using strings like fòô bàř'),
            array('numbers-1234', 'numbers 1234'),
            array('perevirka-ryadka', 'перевірка рядка'),
            array('bukvar-s-bukvoy-y', 'букварь с буквой ы'),
            array('podekhal-k-podezdu-moego-doma', 'подъехал к подъезду моего дома'),
            array('foo:bar:baz', 'Foo bar baz', ':'),
            array('a_string_with_underscores', 'A_string with_underscores', '_'),
            array('a_string_with_dashes', 'A string-with-dashes', '_'),
            array('a\string\with\dashes', 'A string-with-dashes', '\\'),
            array('an_odd_string', '--   An odd__   string-_', '_')
        );
    }
    public function containsProvider()
    {
        return array(
            array(true, 'Str contains foo bar', 'foo bar'),
            array(true, '12398!@(*%!@# @!%#*&^%',  ' @!%#*&^%'),
            array(true, 'Ο συγγραφέας είπε', 'συγγραφέας', 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'å´¥©', true, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'å˚ ∆', true, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'øœ¬', true, 'UTF-8'),
            array(false, 'Str contains foo bar', 'Foo bar'),
            array(false, 'Str contains foo bar', 'foobar'),
            array(false, 'Str contains foo bar', 'foo bar '),
            array(false, 'Ο συγγραφέας είπε', '  συγγραφέας ', true, 'UTF-8'),
            array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', ' ßå˚', true, 'UTF-8'),
            array(true, 'Str contains foo bar', 'Foo bar', false),
            array(true, '12398!@(*%!@# @!%#*&^%',  ' @!%#*&^%', false),
            array(true, 'Ο συγγραφέας είπε', 'ΣΥΓΓΡΑΦΈΑΣ', false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'Å´¥©', false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'Å˚ ∆', false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'ØŒ¬', false, 'UTF-8'),
            array(false, 'Str contains foo bar', 'foobar', false),
            array(false, 'Str contains foo bar', 'foo bar ', false),
            array(false, 'Ο συγγραφέας είπε', '  συγγραφέας ', false, 'UTF-8'),
            array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', ' ßÅ˚', false, 'UTF-8')
        );
    }
    public function containsAnyProvider()
    {
        $singleNeedle = array_map(function ($array) {
            $array[2] = array($array[2]);
            return $array;
        }, $this->containsProvider());
        $provider = array(
            array(false, 'Str contains foo bar', array()),
            array(true, 'Str contains foo bar', array('foo', 'bar')),
            array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*', '&^%')),
            array(true, 'Ο συγγραφέας είπε', array('συγγρ', 'αφέας'), 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å´¥', '©'), true, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å˚ ', '∆'), true, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('øœ', '¬'), true, 'UTF-8'),
            array(false, 'Str contains foo bar', array('Foo', 'Bar')),
            array(false, 'Str contains foo bar', array('foobar', 'bar ')),
            array(false, 'Str contains foo bar', array('foo bar ', '  foo')),
            array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', '  συγγραφ '), true, 'UTF-8'),
            array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßå˚', ' ß '), true, 'UTF-8'),
            array(true, 'Str contains foo bar', array('Foo bar', 'bar'), false),
            array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*&^%', '*&^%'), false),
            array(true, 'Ο συγγραφέας είπε', array('ΣΥΓΓΡΑΦΈΑΣ', 'ΑΦΈΑ'), false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å´¥©', '¥©'), false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å˚ ∆', ' ∆'), false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('ØŒ¬', 'Œ'), false, 'UTF-8'),
            array(false, 'Str contains foo bar', array('foobar', 'none'), false),
            array(false, 'Str contains foo bar', array('foo bar ', ' ba '), false),
            array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', ' ραφέ '), false, 'UTF-8'),
            array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßÅ˚', ' Å˚ '), false, 'UTF-8'),
        );
        return array_merge($singleNeedle, $provider);
    }
    public function containsAllProvider()
    {
        $singleNeedle = array_map(function ($array) {
            $array[2] = array($array[2]);
            return $array;
        }, $this->containsProvider());
        $provider = array(
            array(false, 'Str contains foo bar', array()),
            array(true, 'Str contains foo bar', array('foo', 'bar')),
            array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*', '&^%')),
            array(true, 'Ο συγγραφέας είπε', array('συγγρ', 'αφέας'), 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å´¥', '©'), true, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å˚ ', '∆'), true, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('øœ', '¬'), true, 'UTF-8'),
            array(false, 'Str contains foo bar', array('Foo', 'bar')),
            array(false, 'Str contains foo bar', array('foobar', 'bar')),
            array(false, 'Str contains foo bar', array('foo bar ', 'bar')),
            array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', '  συγγραφ '), true, 'UTF-8'),
            array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßå˚', ' ß '), true, 'UTF-8'),
            array(true, 'Str contains foo bar', array('Foo bar', 'bar'), false),
            array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*&^%', '*&^%'), false),
            array(true, 'Ο συγγραφέας είπε', array('ΣΥΓΓΡΑΦΈΑΣ', 'ΑΦΈΑ'), false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å´¥©', '¥©'), false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å˚ ∆', ' ∆'), false, 'UTF-8'),
            array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('ØŒ¬', 'Œ'), false, 'UTF-8'),
            array(false, 'Str contains foo bar', array('foobar', 'none'), false),
            array(false, 'Str contains foo bar', array('foo bar ', ' ba'), false),
            array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', ' ραφέ '), false, 'UTF-8'),
            array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßÅ˚', ' Å˚ '), false, 'UTF-8'),
        );
        return array_merge($singleNeedle, $provider);
    }
    public function surroundProvider()
    {
        return array(
            array('__foobar__', 'foobar', '__'),
            array('test', 'test', ''),
            array('**', '', '*'),
            array('¬fòô bàř¬', 'fòô bàř', '¬'),
            array('ßå∆˚ test ßå∆˚', ' test ', 'ßå∆˚')
        );
    }
    public function insertProvider()
    {
        return array(
            array('foo bar', 'oo bar', 'f', 0),
            array('foo bar', 'f bar', 'oo', 1),
            array('f bar', 'f bar', 'oo', 20),
            array('foo bar', 'foo ba', 'r', 6),
            array('fòô bàř', 'òô bàř', 'f', 0, 'UTF-8'),
            array('fòô bàř', 'f bàř', 'òô', 1, 'UTF-8'),
            array('fòô bàř', 'fòô bà', 'ř', 6, 'UTF-8')
        );
    }
    public function truncateProvider()
    {
        return array(
            array('Test foo bar', 'Test foo bar', 12),
            array('Test foo ba', 'Test foo bar', 11),
            array('Test foo', 'Test foo bar', 8),
            array('Test fo', 'Test foo bar', 7),
            array('Test', 'Test foo bar', 4),
            array('Test foo bar', 'Test foo bar', 12, '...'),
            array('Test foo...', 'Test foo bar', 11, '...'),
            array('Test ...', 'Test foo bar', 8, '...'),
            array('Test...', 'Test foo bar', 7, '...'),
            array('T...', 'Test foo bar', 4, '...'),
            array('Test fo....', 'Test foo bar', 11, '....'),
            array('Test fòô bàř', 'Test fòô bàř', 12, '', 'UTF-8'),
            array('Test fòô bà', 'Test fòô bàř', 11, '', 'UTF-8'),
            array('Test fòô', 'Test fòô bàř', 8, '', 'UTF-8'),
            array('Test fò', 'Test fòô bàř', 7, '', 'UTF-8'),
            array('Test', 'Test fòô bàř', 4, '', 'UTF-8'),
            array('Test fòô bàř', 'Test fòô bàř', 12, 'ϰϰ', 'UTF-8'),
            array('Test fòô ϰϰ', 'Test fòô bàř', 11, 'ϰϰ', 'UTF-8'),
            array('Test fϰϰ', 'Test fòô bàř', 8, 'ϰϰ', 'UTF-8'),
            array('Test ϰϰ', 'Test fòô bàř', 7, 'ϰϰ', 'UTF-8'),
            array('Teϰϰ', 'Test fòô bàř', 4, 'ϰϰ', 'UTF-8'),
            array('What are your pl...', 'What are your plans today?', 19, '...')
        );
    }
    public function safeTruncateProvider()
    {
        return array(
            array('Test foo bar', 'Test foo bar', 12),
            array('Test foo', 'Test foo bar', 11),
            array('Test foo', 'Test foo bar', 8),
            array('Test', 'Test foo bar', 7),
            array('Test', 'Test foo bar', 4),
            array('Test foo bar', 'Test foo bar', 12, '...'),
            array('Test foo...', 'Test foo bar', 11, '...'),
            array('Test...', 'Test foo bar', 8, '...'),
            array('Test...', 'Test foo bar', 7, '...'),
            array('...', 'Test foo bar', 4, '...'),
            array('Test....', 'Test foo bar', 11, '....'),
            array('Test fòô bàř', 'Test fòô bàř', 12, '', 'UTF-8'),
            array('Test fòô', 'Test fòô bàř', 11, '', 'UTF-8'),
            array('Test fòô', 'Test fòô bàř', 8, '', 'UTF-8'),
            array('Test', 'Test fòô bàř', 7, '', 'UTF-8'),
            array('Test', 'Test fòô bàř', 4, '', 'UTF-8'),
            array('Test fòô bàř', 'Test fòô bàř', 12, 'ϰϰ', 'UTF-8'),
            array('Test fòôϰϰ', 'Test fòô bàř', 11, 'ϰϰ', 'UTF-8'),
            array('Testϰϰ', 'Test fòô bàř', 8, 'ϰϰ', 'UTF-8'),
            array('Testϰϰ', 'Test fòô bàř', 7, 'ϰϰ', 'UTF-8'),
            array('ϰϰ', 'Test fòô bàř', 4, 'ϰϰ', 'UTF-8'),
            array('What are your plans...', 'What are your plans today?', 22, '...')
        );
    }
    public function reverseProvider()
    {
        return array(
            array('', ''),
            array('raboof', 'foobar'),
            array('řàbôòf', 'fòôbàř', 'UTF-8'),
            array('řàb ôòf', 'fòô bàř', 'UTF-8'),
            array('∂∆ ˚åß', 'ßå˚ ∆∂', 'UTF-8')
        );
    }
    public function shuffleProvider()
    {
        return array(
            array('foo bar'),
            array('∂∆ ˚åß', 'UTF-8'),
            array('å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'UTF-8')
        );
    }
    public function trimProvider()
    {
        return array(
            array('foo   bar', '  foo   bar  '),
            array('foo bar', ' foo bar'),
            array('foo bar', 'foo bar '),
            array('foo bar', "\n\t foo bar \n\t"),
            array('fòô   bàř', '  fòô   bàř  '),
            array('fòô bàř', ' fòô bàř'),
            array('fòô bàř', 'fòô bàř '),
            array('fòô bàř', "\n\t fòô bàř \n\t")
        );
    }
    public function longestCommonPrefixProvider()
    {
        return array(
            array('foo', 'foobar', 'foo bar'),
            array('foo bar', 'foo bar', 'foo bar'),
            array('f', 'foo bar', 'far boo'),
            array('', 'toy car', 'foo bar'),
            array('', 'foo bar', ''),
            array('fòô', 'fòôbar', 'fòô bar', 'UTF-8'),
            array('fòô bar', 'fòô bar', 'fòô bar', 'UTF-8'),
            array('fò', 'fòô bar', 'fòr bar', 'UTF-8'),
            array('', 'toy car', 'fòô bar', 'UTF-8'),
            array('', 'fòô bar', '', 'UTF-8'),
        );
    }
    public function longestCommonSuffixProvider()
    {
        return array(
            array('bar', 'foobar', 'foo bar'),
            array('foo bar', 'foo bar', 'foo bar'),
            array('ar', 'foo bar', 'boo far'),
            array('', 'foo bad', 'foo bar'),
            array('', 'foo bar', ''),
            array('bàř', 'fòôbàř', 'fòô bàř', 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 'fòô bàř', 'UTF-8'),
            array(' bàř', 'fòô bàř', 'fòr bàř', 'UTF-8'),
            array('', 'toy car', 'fòô bàř', 'UTF-8'),
            array('', 'fòô bàř', '', 'UTF-8'),
        );
    }
    public function longestCommonSubstringProvider()
    {
        return array(
            array('foo', 'foobar', 'foo bar'),
            array('foo bar', 'foo bar', 'foo bar'),
            array('oo ', 'foo bar', 'boo far'),
            array('foo ba', 'foo bad', 'foo bar'),
            array('', 'foo bar', ''),
            array('fòô', 'fòôbàř', 'fòô bàř', 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 'fòô bàř', 'UTF-8'),
            array(' bàř', 'fòô bàř', 'fòr bàř', 'UTF-8'),
            array(' ', 'toy car', 'fòô bàř', 'UTF-8'),
            array('', 'fòô bàř', '', 'UTF-8'),
        );
    }
    public function lengthProvider()
    {
        return array(
            array(11, '  foo bar  '),
            array(1, 'f'),
            array(0, ''),
            array(7, 'fòô bàř', 'UTF-8')
        );
    }
    public function substrProvider()
    {
        return array(
            array('foo bar', 'foo bar', 0),
            array('bar', 'foo bar', 4),
            array('bar', 'foo bar', 4, null),
            array('o b', 'foo bar', 2, 3),
            array('', 'foo bar', 4, 0),
            array('fòô bàř', 'fòô bàř', 0, null, 'UTF-8'),
            array('bàř', 'fòô bàř', 4, null, 'UTF-8'),
            array('ô b', 'fòô bàř', 2, 3, 'UTF-8'),
            array('', 'fòô bàř', 4, 0, 'UTF-8')
        );
    }
    public function atProvider()
    {
        return array(
            array('f', 'foo bar', 0),
            array('o', 'foo bar', 1),
            array('r', 'foo bar', 6),
            array('', 'foo bar', 7),
            array('f', 'fòô bàř', 0, 'UTF-8'),
            array('ò', 'fòô bàř', 1, 'UTF-8'),
            array('ř', 'fòô bàř', 6, 'UTF-8'),
            array('', 'fòô bàř', 7, 'UTF-8'),
        );
    }
    public function firstProvider()
    {
        return array(
            array('', 'foo bar', -5),
            array('', 'foo bar', 0),
            array('f', 'foo bar', 1),
            array('foo', 'foo bar', 3),
            array('foo bar', 'foo bar', 7),
            array('foo bar', 'foo bar', 8),
            array('', 'fòô bàř', -5, 'UTF-8'),
            array('', 'fòô bàř', 0, 'UTF-8'),
            array('f', 'fòô bàř', 1, 'UTF-8'),
            array('fòô', 'fòô bàř', 3, 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 7, 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 8, 'UTF-8'),
        );
    }
    public function lastProvider()
    {
        return array(
            array('', 'foo bar', -5),
            array('', 'foo bar', 0),
            array('r', 'foo bar', 1),
            array('bar', 'foo bar', 3),
            array('foo bar', 'foo bar', 7),
            array('foo bar', 'foo bar', 8),
            array('', 'fòô bàř', -5, 'UTF-8'),
            array('', 'fòô bàř', 0, 'UTF-8'),
            array('ř', 'fòô bàř', 1, 'UTF-8'),
            array('bàř', 'fòô bàř', 3, 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 7, 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 8, 'UTF-8'),
        );
    }
    public function ensureLeftProvider()
    {
        return array(
            array('foobar', 'foobar', 'f'),
            array('foobar', 'foobar', 'foo'),
            array('foo/foobar', 'foobar', 'foo/'),
            array('http:
            array('http:
            array('fòôbàř', 'fòôbàř', 'f', 'UTF-8'),
            array('fòôbàř', 'fòôbàř', 'fòô', 'UTF-8'),
            array('fòô/fòôbàř', 'fòôbàř', 'fòô/', 'UTF-8'),
            array('http:
            array('http:
        );
    }
    public function ensureRightProvider()
    {
        return array(
            array('foobar', 'foobar', 'r'),
            array('foobar', 'foobar', 'bar'),
            array('foobar/bar', 'foobar', '/bar'),
            array('foobar.com/', 'foobar', '.com/'),
            array('foobar.com/', 'foobar.com/', '.com/'),
            array('fòôbàř', 'fòôbàř', 'ř', 'UTF-8'),
            array('fòôbàř', 'fòôbàř', 'bàř', 'UTF-8'),
            array('fòôbàř/bàř', 'fòôbàř', '/bàř', 'UTF-8'),
            array('fòôbàř.com/', 'fòôbàř', '.com/', 'UTF-8'),
            array('fòôbàř.com/', 'fòôbàř.com/', '.com/', 'UTF-8'),
        );
    }
    public function removeLeftProvider()
    {
        return array(
            array('foo bar', 'foo bar', ''),
            array('oo bar', 'foo bar', 'f'),
            array('bar', 'foo bar', 'foo '),
            array('foo bar', 'foo bar', 'oo'),
            array('foo bar', 'foo bar', 'oo bar'),
            array('oo bar', 'foo bar', Stringy::create('foo bar')->first(1), 'UTF-8'),
            array('oo bar', 'foo bar', Stringy::create('foo bar')->at(0), 'UTF-8'),
            array('fòô bàř', 'fòô bàř', '', 'UTF-8'),
            array('òô bàř', 'fòô bàř', 'f', 'UTF-8'),
            array('bàř', 'fòô bàř', 'fòô ', 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 'òô', 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 'òô bàř', 'UTF-8')
        );
    }
    public function removeRightProvider()
    {
        return array(
            array('foo bar', 'foo bar', ''),
            array('foo ba', 'foo bar', 'r'),
            array('foo', 'foo bar', ' bar'),
            array('foo bar', 'foo bar', 'ba'),
            array('foo bar', 'foo bar', 'foo ba'),
            array('foo ba', 'foo bar', Stringy::create('foo bar')->last(1), 'UTF-8'),
            array('foo ba', 'foo bar', Stringy::create('foo bar')->at(6), 'UTF-8'),
            array('fòô bàř', 'fòô bàř', '', 'UTF-8'),
            array('fòô bà', 'fòô bàř', 'ř', 'UTF-8'),
            array('fòô', 'fòô bàř', ' bàř', 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 'bà', 'UTF-8'),
            array('fòô bàř', 'fòô bàř', 'fòô bà', 'UTF-8')
        );
    }
    public function isAlphaProvider()
    {
        return array(
            array(true, ''),
            array(true, 'foobar'),
            array(false, 'foo bar'),
            array(false, 'foobar2'),
            array(true, 'fòôbàř', 'UTF-8'),
            array(false, 'fòô bàř', 'UTF-8'),
            array(false, 'fòôbàř2', 'UTF-8'),
            array(true, 'ҠѨњфгШ', 'UTF-8'),
            array(false, 'ҠѨњ¨ˆфгШ', 'UTF-8'),
            array(true, '丹尼爾', 'UTF-8')
        );
    }
    public function isAlphanumericProvider()
    {
        return array(
            array(true, ''),
            array(true, 'foobar1'),
            array(false, 'foo bar'),
            array(false, 'foobar2"'),
            array(false, "\nfoobar\n"),
            array(true, 'fòôbàř1', 'UTF-8'),
            array(false, 'fòô bàř', 'UTF-8'),
            array(false, 'fòôbàř2"', 'UTF-8'),
            array(true, 'ҠѨњфгШ', 'UTF-8'),
            array(false, 'ҠѨњ¨ˆфгШ', 'UTF-8'),
            array(true, '丹尼爾111', 'UTF-8'),
            array(true, 'دانيال1', 'UTF-8'),
            array(false, 'دانيال1 ', 'UTF-8')
        );
    }
    public function isBlankProvider()
    {
        return array(
            array(true, ''),
            array(true, ' '),
            array(true, "\n\t "),
            array(true, "\n\t  \v\f"),
            array(false, "\n\t a \v\f"),
            array(false, "\n\t ' \v\f"),
            array(false, "\n\t 2 \v\f"),
            array(true, '', 'UTF-8'),
            array(true, ' ', 'UTF-8'), 
            array(true, '           ', 'UTF-8'), 
            array(true, ' ', 'UTF-8'), 
            array(true, ' ', 'UTF-8'), 
            array(true, '　', 'UTF-8'), 
            array(false, '　z', 'UTF-8'),
            array(false, '　1', 'UTF-8'),
        );
    }
    public function isJsonProvider()
    {
        return array(
            array(true, ''),
            array(true, '123'),
            array(true, '{"foo": "bar"}'),
            array(false, '{"foo":"bar",}'),
            array(false, '{"foo"}'),
            array(true, '["foo"]'),
            array(false, '{"foo": "bar"]'),
            array(true, '123', 'UTF-8'),
            array(true, '{"fòô": "bàř"}', 'UTF-8'),
            array(false, '{"fòô":"bàř",}', 'UTF-8'),
            array(false, '{"fòô"}', 'UTF-8'),
            array(false, '["fòô": "bàř"]', 'UTF-8'),
            array(true, '["fòô"]', 'UTF-8'),
            array(false, '{"fòô": "bàř"]', 'UTF-8'),
        );
    }
    public function isLowerCaseProvider()
    {
        return array(
            array(true, ''),
            array(true, 'foobar'),
            array(false, 'foo bar'),
            array(false, 'Foobar'),
            array(true, 'fòôbàř', 'UTF-8'),
            array(false, 'fòôbàř2', 'UTF-8'),
            array(false, 'fòô bàř', 'UTF-8'),
            array(false, 'fòôbÀŘ', 'UTF-8'),
        );
    }
    public function hasLowerCaseProvider()
    {
        return array(
            array(false, ''),
            array(true, 'foobar'),
            array(false, 'FOO BAR'),
            array(true, 'fOO BAR'),
            array(true, 'foO BAR'),
            array(true, 'FOO BAr'),
            array(true, 'Foobar'),
            array(false, 'FÒÔBÀŘ', 'UTF-8'),
            array(true, 'fòôbàř', 'UTF-8'),
            array(true, 'fòôbàř2', 'UTF-8'),
            array(true, 'Fòô bàř', 'UTF-8'),
            array(true, 'fòôbÀŘ', 'UTF-8'),
        );
    }
    public function isSerializedProvider()
    {
        return array(
            array(false, ''),
            array(true, 'a:1:{s:3:"foo";s:3:"bar";}'),
            array(false, 'a:1:{s:3:"foo";s:3:"bar"}'),
            array(true, serialize(array('foo' => 'bar'))),
            array(true, 'a:1:{s:5:"fòô";s:5:"bàř";}', 'UTF-8'),
            array(false, 'a:1:{s:5:"fòô";s:5:"bàř"}', 'UTF-8'),
            array(true, serialize(array('fòô' => 'bár')), 'UTF-8'),
        );
    }
    public function isUpperCaseProvider()
    {
        return array(
            array(true, ''),
            array(true, 'FOOBAR'),
            array(false, 'FOO BAR'),
            array(false, 'fOOBAR'),
            array(true, 'FÒÔBÀŘ', 'UTF-8'),
            array(false, 'FÒÔBÀŘ2', 'UTF-8'),
            array(false, 'FÒÔ BÀŘ', 'UTF-8'),
            array(false, 'FÒÔBàř', 'UTF-8'),
        );
    }
    public function hasUpperCaseProvider()
    {
        return array(
            array(false, ''),
            array(true, 'FOOBAR'),
            array(false, 'foo bar'),
            array(true, 'Foo bar'),
            array(true, 'FOo bar'),
            array(true, 'foo baR'),
            array(true, 'fOOBAR'),
            array(false, 'fòôbàř', 'UTF-8'),
            array(true, 'FÒÔBÀŘ', 'UTF-8'),
            array(true, 'FÒÔBÀŘ2', 'UTF-8'),
            array(true, 'fÒÔ BÀŘ', 'UTF-8'),
            array(true, 'FÒÔBàř', 'UTF-8'),
        );
    }
    public function isHexadecimalProvider()
    {
        return array(
            array(true, ''),
            array(true, 'abcdef'),
            array(true, 'ABCDEF'),
            array(true, '0123456789'),
            array(true, '0123456789AbCdEf'),
            array(false, '0123456789x'),
            array(false, 'ABCDEFx'),
            array(true, 'abcdef', 'UTF-8'),
            array(true, 'ABCDEF', 'UTF-8'),
            array(true, '0123456789', 'UTF-8'),
            array(true, '0123456789AbCdEf', 'UTF-8'),
            array(false, '0123456789x', 'UTF-8'),
            array(false, 'ABCDEFx', 'UTF-8'),
        );
    }
    public function countSubstrProvider()
    {
        return array(
            array(0, '', 'foo'),
            array(0, 'foo', 'bar'),
            array(1, 'foo bar', 'foo'),
            array(2, 'foo bar', 'o'),
            array(0, '', 'fòô', 'UTF-8'),
            array(0, 'fòô', 'bàř', 'UTF-8'),
            array(1, 'fòô bàř', 'fòô', 'UTF-8'),
            array(2, 'fôòô bàř', 'ô', 'UTF-8'),
            array(0, 'fÔÒÔ bàř', 'ô', 'UTF-8'),
            array(0, 'foo', 'BAR', false),
            array(1, 'foo bar', 'FOo', false),
            array(2, 'foo bar', 'O', false),
            array(1, 'fòô bàř', 'fÒÔ', false, 'UTF-8'),
            array(2, 'fôòô bàř', 'Ô', false, 'UTF-8'),
            array(2, 'συγγραφέας', 'Σ', false, 'UTF-8')
        );
    }
    public function replaceProvider()
    {
        return array(
            array('', '', '', ''),
            array('foo', '', '', 'foo'),
            array('foo', '\s', '\s', 'foo'),
            array('foo bar', 'foo bar', '', ''),
            array('foo bar', 'foo bar', 'f(o)o', '\1'),
            array('\1 bar', 'foo bar', 'foo', '\1'),
            array('bar', 'foo bar', 'foo ', ''),
            array('far bar', 'foo bar', 'foo', 'far'),
            array('bar bar', 'foo bar foo bar', 'foo ', ''),
            array('', '', '', '', 'UTF-8'),
            array('fòô', '', '', 'fòô', 'UTF-8'),
            array('fòô', '\s', '\s', 'fòô', 'UTF-8'),
            array('fòô bàř', 'fòô bàř', '', '', 'UTF-8'),
            array('bàř', 'fòô bàř', 'fòô ', '', 'UTF-8'),
            array('far bàř', 'fòô bàř', 'fòô', 'far', 'UTF-8'),
            array('bàř bàř', 'fòô bàř fòô bàř', 'fòô ', '', 'UTF-8'),
        );
    }
    public function regexReplaceProvider()
    {
        return array(
            array('', '', '', ''),
            array('bar', 'foo', 'f[o]+', 'bar'),
            array('o bar', 'foo bar', 'f(o)o', '\1'),
            array('bar', 'foo bar', 'f[O]+\s', '', 'i'),
            array('foo', 'bar', '[[:alpha:]]{3}', 'foo'),
            array('', '', '', '', 'msr', 'UTF-8'),
            array('bàř', 'fòô ', 'f[òô]+\s', 'bàř', 'msr', 'UTF-8'),
            array('fòô', 'bàř', '[[:alpha:]]{3}', 'fòô', 'msr', 'UTF-8')
        );
    }
}
