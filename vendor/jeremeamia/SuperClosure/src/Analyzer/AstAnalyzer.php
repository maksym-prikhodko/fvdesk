<?php namespace SuperClosure\Analyzer;
use SuperClosure\Analyzer\Visitor\ThisDetectorVisitor;
use SuperClosure\Exception\ClosureAnalysisException;
use SuperClosure\Analyzer\Visitor\ClosureLocatorVisitor;
use SuperClosure\Analyzer\Visitor\MagicConstantVisitor;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard as NodePrinter;
use PhpParser\Error as ParserError;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser as CodeParser;
use PhpParser\Lexer\Emulative as EmulativeLexer;
class AstAnalyzer extends ClosureAnalyzer
{
    protected function determineCode(array &$data)
    {
        $this->locateClosure($data);
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new MagicConstantVisitor($data['location']));
        $traverser->addVisitor($thisDetector = new ThisDetectorVisitor);
        $data['ast'] = $traverser->traverse([$data['ast']])[0];
        $data['hasThis'] = $thisDetector->detected;
        $data['code'] = (new NodePrinter)->prettyPrint([$data['ast']]);
    }
    private function locateClosure(array &$data)
    {
        try {
            $locator = new ClosureLocatorVisitor($data['reflection']);
            $fileAst = $this->getFileAst($data['reflection']);
            $fileTraverser = new NodeTraverser;
            $fileTraverser->addVisitor(new NameResolver);
            $fileTraverser->addVisitor($locator);
            $fileTraverser->traverse($fileAst);
        } catch (ParserError $e) {
            throw new ClosureAnalysisException(
                'There was an error analyzing the closure code.', 0, $e
            );
        }
        $data['ast'] = $locator->closureNode;
        if (!$data['ast']) {
            throw new ClosureAnalysisException(
                'The closure was not found within the abstract syntax tree.'
            );
        }
        $data['location'] = $locator->location;
    }
    protected function determineContext(array &$data)
    {
        $refs = 0;
        $vars = array_map(function ($node) use (&$refs) {
            if ($node->byRef) {
                $refs++;
            }
            return $node->var;
        }, $data['ast']->uses);
        $data['hasRefs'] = ($refs > 0);
        $values = $data['reflection']->getStaticVariables();
        foreach ($vars as $name) {
            if (isset($values[$name])) {
                $data['context'][$name] = $values[$name];
            }
        }
    }
    private function getFileAst(\ReflectionFunction $reflection)
    {
        $fileName = $reflection->getFileName();
        if (!file_exists($fileName)) {
            throw new ClosureAnalysisException(
                "The file containing the closure, \"{$fileName}\" did not exist."
            );
        }
        $parser = new CodeParser(new EmulativeLexer);
        return $parser->parse(file_get_contents($fileName));
    }
}
