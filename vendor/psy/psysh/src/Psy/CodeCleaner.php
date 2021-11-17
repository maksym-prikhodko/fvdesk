<?php
namespace Psy;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard as Printer;
use Psy\CodeCleaner\AbstractClassPass;
use Psy\CodeCleaner\AssignThisVariablePass;
use Psy\CodeCleaner\CalledClassPass;
use Psy\CodeCleaner\CallTimePassByReferencePass;
use Psy\CodeCleaner\FunctionReturnInWriteContextPass;
use Psy\CodeCleaner\ImplicitReturnPass;
use Psy\CodeCleaner\InstanceOfPass;
use Psy\CodeCleaner\LeavePsyshAlonePass;
use Psy\CodeCleaner\LegacyEmptyPass;
use Psy\CodeCleaner\MagicConstantsPass;
use Psy\CodeCleaner\NamespacePass;
use Psy\CodeCleaner\StaticConstructorPass;
use Psy\CodeCleaner\UseStatementPass;
use Psy\CodeCleaner\ValidClassNamePass;
use Psy\CodeCleaner\ValidConstantPass;
use Psy\CodeCleaner\ValidFunctionNamePass;
use Psy\Exception\ParseErrorException;
class CodeCleaner
{
    private $parser;
    private $printer;
    private $traverser;
    private $namespace;
    public function __construct(Parser $parser = null, Printer $printer = null, NodeTraverser $traverser = null)
    {
        $this->parser    = $parser    ?: new Parser(new Lexer());
        $this->printer   = $printer   ?: new Printer();
        $this->traverser = $traverser ?: new NodeTraverser();
        foreach ($this->getDefaultPasses() as $pass) {
            $this->traverser->addVisitor($pass);
        }
    }
    private function getDefaultPasses()
    {
        return array(
            new AbstractClassPass(),
            new AssignThisVariablePass(),
            new FunctionReturnInWriteContextPass(),
            new CallTimePassByReferencePass(),
            new CalledClassPass(),
            new InstanceOfPass(),
            new LeavePsyshAlonePass(),
            new LegacyEmptyPass(),
            new ImplicitReturnPass(),
            new UseStatementPass(),      
            new NamespacePass($this),    
            new StaticConstructorPass(),
            new ValidFunctionNamePass(),
            new ValidClassNamePass(),
            new ValidConstantPass(),
            new MagicConstantsPass(),
        );
    }
    public function clean(array $codeLines, $requireSemicolons = false)
    {
        $stmts = $this->parse("<?php " . implode(PHP_EOL, $codeLines) . PHP_EOL, $requireSemicolons);
        if ($stmts === false) {
            return false;
        }
        $stmts = $this->traverser->traverse($stmts);
        return $this->printer->prettyPrint($stmts);
    }
    public function setNamespace(array $namespace = null)
    {
        $this->namespace = $namespace;
    }
    public function getNamespace()
    {
        return $this->namespace;
    }
    protected function parse($code, $requireSemicolons = false)
    {
        try {
            return $this->parser->parse($code);
        } catch (\PhpParser\Error $e) {
            if (!$this->parseErrorIsEOF($e)) {
                throw ParseErrorException::fromParseError($e);
            }
            if ($requireSemicolons) {
                return false;
            }
            try {
                return $this->parser->parse($code . ';');
            } catch (\PhpParser\Error $e) {
                return false;
            }
        }
    }
    private function parseErrorIsEOF(\PhpParser\Error $e)
    {
        $msg = $e->getRawMessage();
        return ($msg === "Unexpected token EOF") || (strpos($msg, "Syntax error, unexpected EOF") !== false);
    }
}
