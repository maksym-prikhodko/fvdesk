<?php
namespace PhpParser;
class LexerTest extends \PHPUnit_Framework_TestCase
{
    protected function getLexer(array $options = array()) {
        return new Lexer($options);
    }
    public function testError($code, $message) {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM does not throw warnings from token_get_all()');
        }
        $lexer = $this->getLexer();
        try {
            $lexer->startLexing($code);
        } catch (Error $e) {
            $this->assertSame($message, $e->getMessage());
            return;
        }
        $this->fail('Expected PhpParser\Error');
    }
    public function provideTestError() {
        return array(
            array('<?php 
    public function testLex($code, $options, $tokens) {
        $lexer = $this->getLexer($options);
        $lexer->startLexing($code);
        while ($id = $lexer->getNextToken($value, $startAttributes, $endAttributes)) {
            $token = array_shift($tokens);
            $this->assertSame($token[0], $id);
            $this->assertSame($token[1], $value);
            $this->assertEquals($token[2], $startAttributes);
            $this->assertEquals($token[3], $endAttributes);
        }
    }
    public function provideTestLex() {
        return array(
            array(
                '<?php tokens ?>plaintext',
                array(),
                array(
                    array(
                        Parser::T_STRING, 'tokens',
                        array('startLine' => 1), array('endLine' => 1)
                    ),
                    array(
                        ord(';'), '?>',
                        array('startLine' => 1), array('endLine' => 1)
                    ),
                    array(
                        Parser::T_INLINE_HTML, 'plaintext',
                        array('startLine' => 1), array('endLine' => 1)
                    ),
                )
            ),
            array(
                '<?php' . "\n" . '$ token  $',
                array(),
                array(
                    array(
                        ord('$'), '$',
                        array('startLine' => 2), array('endLine' => 2)
                    ),
                    array(
                        Parser::T_STRING, 'token',
                        array('startLine' => 2), array('endLine' => 2)
                    ),
                    array(
                        ord('$'), '$',
                        array(
                            'startLine' => 3,
                            'comments' => array(new Comment\Doc('', 2))
                        ),
                        array('endLine' => 3)
                    ),
                )
            ),
            array(
                '<?php  
                array(),
                array(
                    array(
                        Parser::T_STRING, 'token',
                        array(
                            'startLine' => 2,
                            'comments' => array(
                                new Comment('', 1),
                                new Comment('
                                new Comment\Doc('', 2),
                                new Comment\Doc('', 2),
                            ),
                        ),
                        array('endLine' => 2)
                    ),
                )
            ),
            array(
                '<?php "foo' . "\n" . 'bar"',
                array(),
                array(
                    array(
                        Parser::T_CONSTANT_ENCAPSED_STRING, '"foo' . "\n" . 'bar"',
                        array('startLine' => 1), array('endLine' => 2)
                    ),
                )
            ),
            array(
                '<?php "a";' . "\n" . '
                array('usedAttributes' => array('startFilePos', 'endFilePos')),
                array(
                    array(
                        Parser::T_CONSTANT_ENCAPSED_STRING, '"a"',
                        array('startFilePos' => 6), array('endFilePos' => 8)
                    ),
                    array(
                        ord(';'), ';',
                        array('startFilePos' => 9), array('endFilePos' => 9)
                    ),
                    array(
                        Parser::T_CONSTANT_ENCAPSED_STRING, '"b"',
                        array('startFilePos' => 18), array('endFilePos' => 20)
                    ),
                    array(
                        ord(';'), ';',
                        array('startFilePos' => 21), array('endFilePos' => 21)
                    ),
                )
            ),
            array(
                '<?php "a";' . "\n" . '
                array('usedAttributes' => array('startTokenPos', 'endTokenPos')),
                array(
                    array(
                        Parser::T_CONSTANT_ENCAPSED_STRING, '"a"',
                        array('startTokenPos' => 1), array('endTokenPos' => 1)
                    ),
                    array(
                        ord(';'), ';',
                        array('startTokenPos' => 2), array('endTokenPos' => 2)
                    ),
                    array(
                        Parser::T_CONSTANT_ENCAPSED_STRING, '"b"',
                        array('startTokenPos' => 5), array('endTokenPos' => 5)
                    ),
                    array(
                        ord(';'), ';',
                        array('startTokenPos' => 6), array('endTokenPos' => 6)
                    ),
                )
            ),
            array(
                '<?php  $bar;',
                array('usedAttributes' => array()),
                array(
                    array(
                        Parser::T_VARIABLE, '$bar',
                        array(), array()
                    ),
                    array(
                        ord(';'), ';',
                        array(), array()
                    )
                )
            )
        );
    }
    public function testHandleHaltCompiler($code, $remaining) {
        $lexer = $this->getLexer();
        $lexer->startLexing($code);
        while (Parser::T_HALT_COMPILER !== $lexer->getNextToken());
        $this->assertSame($remaining, $lexer->handleHaltCompiler());
        $this->assertSame(0, $lexer->getNextToken());
    }
    public function provideTestHaltCompiler() {
        return array(
            array('<?php ... __halt_compiler();Remaining Text', 'Remaining Text'),
            array('<?php ... __halt_compiler ( ) ;Remaining Text', 'Remaining Text'),
            array('<?php ... __halt_compiler() ?>Remaining Text', 'Remaining Text'),
        );
    }
    public function testGetTokens() {
        $code = '<?php "a";' . "\n" . '
        $expectedTokens = array(
            array(T_OPEN_TAG, '<?php ', 1),
            array(T_CONSTANT_ENCAPSED_STRING, '"a"', 1),
            ';',
            array(T_WHITESPACE, "\n", 1),
            array(T_COMMENT, '
            array(T_CONSTANT_ENCAPSED_STRING, '"b"', 3),
            ';',
        );
        $lexer = $this->getLexer();
        $lexer->startLexing($code);
        $this->assertSame($expectedTokens, $lexer->getTokens());
    }
}
