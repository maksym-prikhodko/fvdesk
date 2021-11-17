<?php
namespace PhpParser;
abstract class ParserAbstract
{
    const SYMBOL_NONE = -1;
    protected $tokenToSymbolMapSize;
    protected $actionTableSize;
    protected $gotoTableSize;
    protected $invalidSymbol;
    protected $defaultAction;
    protected $unexpectedTokenRule;
    protected $YY2TBLSTATE;
    protected $YYNLSTATES;
    protected $tokenToSymbol;
    protected $symbolToName;
    protected $productions;
    protected $actionBase;
    protected $action;
    protected $actionCheck;
    protected $actionDefault;
    protected $gotoBase;
    protected $goto;
    protected $gotoCheck;
    protected $gotoDefault;
    protected $ruleToNonTerminal;
    protected $ruleToLength;
    protected $lexer;
    protected $semValue;
    protected $semStack;
    protected $stackPos;
    public function __construct(Lexer $lexer) {
        $this->lexer = $lexer;
    }
    public function parse($code) {
        $this->lexer->startLexing($code);
        $symbol = self::SYMBOL_NONE;
        $startAttributes = array('startLine' => 1);
        $endAttributes   = array();
        $attributeStack = array($startAttributes);
        $state = 0;
        $stateStack = array($state);
        $this->semStack = array();
        $this->stackPos = 0;
        for (;;) {
            if ($this->actionBase[$state] == 0) {
                $rule = $this->actionDefault[$state];
            } else {
                if ($symbol === self::SYMBOL_NONE) {
                    $tokenId = $this->lexer->getNextToken($tokenValue, $startAttributes, $nextEndAttributes);
                    $symbol = $tokenId >= 0 && $tokenId < $this->tokenToSymbolMapSize
                        ? $this->tokenToSymbol[$tokenId]
                        : $this->invalidSymbol;
                    if ($symbol === $this->invalidSymbol) {
                        throw new \RangeException(sprintf(
                            'The lexer returned an invalid token (id=%d, value=%s)',
                            $tokenId, $tokenValue
                        ));
                    }
                    $attributeStack[$this->stackPos] = $startAttributes;
                }
                $idx = $this->actionBase[$state] + $symbol;
                if ((($idx >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] == $symbol)
                     || ($state < $this->YY2TBLSTATE
                         && ($idx = $this->actionBase[$state + $this->YYNLSTATES] + $symbol) >= 0
                         && $idx < $this->actionTableSize && $this->actionCheck[$idx] == $symbol))
                    && ($action = $this->action[$idx]) != $this->defaultAction) {
                    if ($action > 0) {
                        ++$this->stackPos;
                        $stateStack[$this->stackPos]     = $state = $action;
                        $this->semStack[$this->stackPos] = $tokenValue;
                        $attributeStack[$this->stackPos] = $startAttributes;
                        $endAttributes = $nextEndAttributes;
                        $symbol = self::SYMBOL_NONE;
                        if ($action < $this->YYNLSTATES)
                            continue;
                        $rule = $action - $this->YYNLSTATES;
                    } else {
                        $rule = -$action;
                    }
                } else {
                    $rule = $this->actionDefault[$state];
                }
            }
            for (;;) {
                if ($rule === 0) {
                    return $this->semValue;
                } elseif ($rule !== $this->unexpectedTokenRule) {
                    try {
                        $this->{'reduceRule' . $rule}(
                            $attributeStack[$this->stackPos - $this->ruleToLength[$rule]]
                            + $endAttributes
                        );
                    } catch (Error $e) {
                        if (-1 === $e->getRawLine() && isset($startAttributes['startLine'])) {
                            $e->setRawLine($startAttributes['startLine']);
                        }
                        throw $e;
                    }
                    $this->stackPos -= $this->ruleToLength[$rule];
                    $nonTerminal = $this->ruleToNonTerminal[$rule];
                    $idx = $this->gotoBase[$nonTerminal] + $stateStack[$this->stackPos];
                    if ($idx >= 0 && $idx < $this->gotoTableSize && $this->gotoCheck[$idx] == $nonTerminal) {
                        $state = $this->goto[$idx];
                    } else {
                        $state = $this->gotoDefault[$nonTerminal];
                    }
                    ++$this->stackPos;
                    $stateStack[$this->stackPos]     = $state;
                    $this->semStack[$this->stackPos] = $this->semValue;
                    $attributeStack[$this->stackPos] = $startAttributes;
                    if ($state < $this->YYNLSTATES)
                        break;
                    $rule = $state - $this->YYNLSTATES;
                } else {
                    if ($expected = $this->getExpectedTokens($state)) {
                        $expectedString = ', expecting ' . implode(' or ', $expected);
                    } else {
                        $expectedString = '';
                    }
                    throw new Error(
                        'Syntax error, unexpected ' . $this->symbolToName[$symbol] . $expectedString,
                        $startAttributes['startLine']
                    );
                }
            }
        }
    }
    protected function getExpectedTokens($state) {
        $expected = array();
        $base = $this->actionBase[$state];
        foreach ($this->symbolToName as $symbol => $name) {
            $idx = $base + $symbol;
            if ($idx >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $symbol
                || $state < $this->YY2TBLSTATE
                && ($idx = $this->actionBase[$state + $this->YYNLSTATES] + $symbol) >= 0
                && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $symbol
            ) {
                if ($this->action[$idx] != $this->unexpectedTokenRule) {
                    if (count($expected) == 4) {
                        return array();
                    }
                    $expected[] = $name;
                }
            }
        }
        return $expected;
    }
    protected function traceNewState($state, $symbol) {
        echo '% State ' . $state
            . ', Lookahead ' . ($symbol == self::SYMBOL_NONE ? '--none--' : $this->symbolToName[$symbol]) . "\n";
    }
    protected function traceRead($symbol) {
        echo '% Reading ' . $this->symbolToName[$symbol] . "\n";
    }
    protected function traceShift($symbol) {
        echo '% Shift ' . $this->symbolToName[$symbol] . "\n";
    }
    protected function traceAccept() {
        echo "% Accepted.\n";
    }
    protected function traceReduce($n) {
        echo '% Reduce by (' . $n . ') ' . $this->productions[$n] . "\n";
    }
    protected function handleNamespaces(array $stmts) {
        $style = $this->getNamespacingStyle($stmts);
        if (null === $style) {
            return $stmts;
        } elseif ('brace' === $style) {
            $afterFirstNamespace = false;
            foreach ($stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\Namespace_) {
                    $afterFirstNamespace = true;
                } elseif (!$stmt instanceof Node\Stmt\HaltCompiler && $afterFirstNamespace) {
                    throw new Error('No code may exist outside of namespace {}', $stmt->getLine());
                }
            }
            return $stmts;
        } else {
            $resultStmts = array();
            $targetStmts =& $resultStmts;
            foreach ($stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\Namespace_) {
                    $stmt->stmts = array();
                    $targetStmts =& $stmt->stmts;
                    $resultStmts[] = $stmt;
                } elseif ($stmt instanceof Node\Stmt\HaltCompiler) {
                    $resultStmts[] = $stmt;
                } else {
                    $targetStmts[] = $stmt;
                }
            }
            return $resultStmts;
        }
    }
    private function getNamespacingStyle(array $stmts) {
        $style = null;
        $hasNotAllowedStmts = false;
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Namespace_) {
                $currentStyle = null === $stmt->stmts ? 'semicolon' : 'brace';
                if (null === $style) {
                    $style = $currentStyle;
                    if ($hasNotAllowedStmts) {
                        throw new Error('Namespace declaration statement has to be the very first statement in the script', $stmt->getLine());
                    }
                } elseif ($style !== $currentStyle) {
                    throw new Error('Cannot mix bracketed namespace declarations with unbracketed namespace declarations', $stmt->getLine());
                }
            } elseif (!$stmt instanceof Node\Stmt\Declare_ && !$stmt instanceof Node\Stmt\HaltCompiler) {
                $hasNotAllowedStmts = true;
            }
        }
        return $style;
    }
}
