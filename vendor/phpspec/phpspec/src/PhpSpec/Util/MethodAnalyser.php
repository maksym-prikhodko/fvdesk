<?php
namespace PhpSpec\Util;
class MethodAnalyser
{
    public function methodIsEmpty($class, $method)
    {
        return $this->reflectionMethodIsEmpty(new \ReflectionMethod($class, $method));
    }
    public function reflectionMethodIsEmpty(\ReflectionMethod $method)
    {
        if ($this->isNotImplementedInPhp($method)) {
            return false;
        }
        $code = $this->getCodeBody($method);
        $codeWithoutComments = $this->stripComments($code);
        return $this->codeIsOnlyBlocksAndWhitespace($codeWithoutComments);
    }
    public function getMethodOwnerName($class, $method)
    {
        $reflectionMethod = new \ReflectionMethod($class, $method);
        $startLine = $reflectionMethod->getStartLine();
        $endLine = $reflectionMethod->getEndLine();
        $reflectionClass  = $this->getMethodOwner($reflectionMethod, $startLine, $endLine);
        return $reflectionClass->getName();
    }
    private function getCodeBody(\ReflectionMethod $reflectionMethod)
    {
        $endLine = $reflectionMethod->getEndLine();
        $startLine = $reflectionMethod->getStartLine();
        $reflectionClass = $this->getMethodOwner($reflectionMethod, $startLine, $endLine);
        $length = $endLine - $startLine;
        $lines = file($reflectionClass->getFileName());
        $code = join(PHP_EOL, array_slice($lines, $startLine - 1, $length + 1));
        return preg_replace('/.*function[^{]+{/s', '', $code);
    }
    private function getMethodOwner(\ReflectionMethod $reflectionMethod, $methodStartLine, $methodEndLine)
    {
        $reflectionClass = $reflectionMethod->getDeclaringClass();
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            return $reflectionClass;
        }
        $fileName = $reflectionMethod->getFileName();
        $trait = $this->getDeclaringTrait($reflectionClass->getTraits(), $fileName, $methodStartLine, $methodEndLine);
        return $trait === null ? $reflectionClass : $trait;
    }
    private function getDeclaringTrait(array $traits, $file, $start, $end)
    {
        foreach ($traits as $trait) {
            if ($trait->getFileName() == $file && $trait->getStartLine() <= $start && $trait->getEndLine() >= $end) {
                return $trait;
            }
            if (null !== ( $trait = $this->getDeclaringTrait($trait->getTraits(), $file, $start, $end) )) {
                return $trait;
            }
        }
        return null;
    }
    private function stripComments($code)
    {
        $tokens = token_get_all('<?php ' . $code);
        $comments = array_map(
            function ($token) {
                return $token[1];
            },
            array_filter(
                $tokens,
                function ($token) {
                    return is_array($token) && in_array($token[0], array(T_COMMENT, T_DOC_COMMENT));
                })
        );
        $commentless = str_replace($comments, '', $code);
        return $commentless;
    }
    private function codeIsOnlyBlocksAndWhitespace($codeWithoutComments)
    {
        return (bool) preg_match('/^[\s{}]*$/s', $codeWithoutComments);
    }
    private function isNotImplementedInPhp(\ReflectionMethod $method)
    {
        $filename = $method->getDeclaringClass()->getFileName();
        if (false === $filename) {
            return true;
        }
        if (preg_match('#^/([:/]systemlib.|/$)#', $filename)) {
            return true;
        }
        return false;
    }
}
