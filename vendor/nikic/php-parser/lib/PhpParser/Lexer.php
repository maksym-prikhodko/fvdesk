<?php
namespace PhpParser;
class Lexer
{
    protected $code;
    protected $tokens;
    protected $pos;
    protected $line;
    protected $filePos;
    protected $tokenMap;
    protected $dropTokens;
    protected $usedAttributes;
    public function __construct(array $options = array()) {
        $this->tokenMap = $this->createTokenMap();
        $this->dropTokens = array_fill_keys(array(T_WHITESPACE, T_OPEN_TAG), 1);
        $options += array(
            'usedAttributes' => array('comments', 'startLine', 'endLine'),
        );
        $this->usedAttributes = array_fill_keys($options['usedAttributes'], true);
    }
    public function startLexing($code) {
        $scream = ini_set('xdebug.scream', '0');
        $this->resetErrors();
        $this->tokens = @token_get_all($code);
        $this->handleErrors();
        if (false !== $scream) {
            ini_set('xdebug.scream', $scream);
        }
        $this->code = $code; 
        $this->pos  = -1;
        $this->line =  1;
        $this->filePos = 0;
    }
    protected function resetErrors() {
        set_error_handler(function() { return false; }, 0);
        @$undefinedVariable;
        restore_error_handler();
    }
    protected function handleErrors() {
        $error = error_get_last();
        if (preg_match(
            '~^Unterminated comment starting line ([0-9]+)$~',
            $error['message'], $matches
        )) {
            throw new Error('Unterminated comment', (int) $matches[1]);
        }
        if (preg_match(
            '~^Unexpected character in input:  \'(.)\' \(ASCII=([0-9]+)\)~s',
            $error['message'], $matches
        )) {
            throw new Error(sprintf(
                'Unexpected character "%s" (ASCII %d)',
                $matches[1], $matches[2]
            ));
        }
        if (preg_match('~^Unexpected character in input:  \'$~', $error['message'])) {
            throw new Error('Unexpected null byte');
        }
    }
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null) {
        $startAttributes = array();
        $endAttributes   = array();
        while (isset($this->tokens[++$this->pos])) {
            $token = $this->tokens[$this->pos];
            if (isset($this->usedAttributes['startTokenPos'])) {
                $startAttributes['startTokenPos'] = $this->pos;
            }
            if (isset($this->usedAttributes['startFilePos'])) {
                $startAttributes['startFilePos'] = $this->filePos;
            }
            if (is_string($token)) {
                if ('b"' === $token) {
                    $value = 'b"';
                    $this->filePos += 2;
                    $id = ord('"');
                } else {
                    $value = $token;
                    $this->filePos += 1;
                    $id = ord($token);
                }
                if (isset($this->usedAttributes['startLine'])) {
                    $startAttributes['startLine'] = $this->line;
                }
                if (isset($this->usedAttributes['endLine'])) {
                    $endAttributes['endLine'] = $this->line;
                }
                if (isset($this->usedAttributes['endTokenPos'])) {
                    $endAttributes['endTokenPos'] = $this->pos;
                }
                if (isset($this->usedAttributes['endFilePos'])) {
                    $endAttributes['endFilePos'] = $this->filePos - 1;
                }
                return $id;
            } else {
                $this->line += substr_count($token[1], "\n");
                $this->filePos += strlen($token[1]);
                if (T_COMMENT === $token[0]) {
                    if (isset($this->usedAttributes['comments'])) {
                        $startAttributes['comments'][] = new Comment($token[1], $token[2]);
                    }
                } elseif (T_DOC_COMMENT === $token[0]) {
                    if (isset($this->usedAttributes['comments'])) {
                        $startAttributes['comments'][] = new Comment\Doc($token[1], $token[2]);
                    }
                } elseif (!isset($this->dropTokens[$token[0]])) {
                    $value = $token[1];
                    if (isset($this->usedAttributes['startLine'])) {
                        $startAttributes['startLine'] = $token[2];
                    }
                    if (isset($this->usedAttributes['endLine'])) {
                        $endAttributes['endLine'] = $this->line;
                    }
                    if (isset($this->usedAttributes['endTokenPos'])) {
                        $endAttributes['endTokenPos'] = $this->pos;
                    }
                    if (isset($this->usedAttributes['endFilePos'])) {
                        $endAttributes['endFilePos'] = $this->filePos - 1;
                    }
                    return $this->tokenMap[$token[0]];
                }
            }
        }
        $startAttributes['startLine'] = $this->line;
        return 0;
    }
    public function getTokens() {
        return $this->tokens;
    }
    public function handleHaltCompiler() {
        $textBefore = '';
        for ($i = 0; $i <= $this->pos; ++$i) {
            if (is_string($this->tokens[$i])) {
                $textBefore .= $this->tokens[$i];
            } else {
                $textBefore .= $this->tokens[$i][1];
            }
        }
        $textAfter = substr($this->code, strlen($textBefore));
        if (!preg_match('~\s*\(\s*\)\s*(?:;|\?>\r?\n?)~', $textAfter, $matches)) {
            throw new Error('__HALT_COMPILER must be followed by "();"');
        }
        $this->pos = count($this->tokens);
        return (string) substr($textAfter, strlen($matches[0])); 
    }
    protected function createTokenMap() {
        $tokenMap = array();
        for ($i = 256; $i < 1000; ++$i) {
            if (T_DOUBLE_COLON === $i) {
                $tokenMap[$i] = Parser::T_PAAMAYIM_NEKUDOTAYIM;
            } elseif(T_OPEN_TAG_WITH_ECHO === $i) {
                $tokenMap[$i] = Parser::T_ECHO;
            } elseif(T_CLOSE_TAG === $i) {
                $tokenMap[$i] = ord(';');
            } elseif ('UNKNOWN' !== $name = token_name($i)) {
                if ('T_HASHBANG' === $name) {
                    $tokenMap[$i] = Parser::T_INLINE_HTML;
                } else if (defined($name = 'PhpParser\Parser::' . $name)) {
                    $tokenMap[$i] = constant($name);
                }
            }
        }
        if (defined('T_ONUMBER')) {
            $tokenMap[T_ONUMBER] = Parser::T_DNUMBER;
        }
        return $tokenMap;
    }
}
