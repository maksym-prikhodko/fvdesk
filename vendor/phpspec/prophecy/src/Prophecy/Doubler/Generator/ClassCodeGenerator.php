<?php
namespace Prophecy\Doubler\Generator;
class ClassCodeGenerator
{
    public function generate($classname, Node\ClassNode $class)
    {
        $parts     = explode('\\', $classname);
        $classname = array_pop($parts);
        $namespace = implode('\\', $parts);
        $code = sprintf("class %s extends \%s implements %s {\n",
            $classname, $class->getParentClass(), implode(', ',
                array_map(function ($interface) {return '\\'.$interface;}, $class->getInterfaces())
            )
        );
        foreach ($class->getProperties() as $name => $visibility) {
            $code .= sprintf("%s \$%s;\n", $visibility, $name);
        }
        $code .= "\n";
        foreach ($class->getMethods() as $method) {
            $code .= $this->generateMethod($method)."\n";
        }
        $code .= "\n}";
        return sprintf("namespace %s {\n%s\n}", $namespace, $code);
    }
    private function generateMethod(Node\MethodNode $method)
    {
        $php = sprintf("%s %s function %s%s(%s) {\n",
            $method->getVisibility(),
            $method->isStatic() ? 'static' : '',
            $method->returnsReference() ? '&':'',
            $method->getName(),
            implode(', ', $this->generateArguments($method->getArguments()))
        );
        $php .= $method->getCode()."\n";
        return $php.'}';
    }
    private function generateArguments(array $arguments)
    {
        return array_map(function (Node\ArgumentNode $argument) {
            $php = '';
            if ($hint = $argument->getTypeHint()) {
                if ('array' === $hint || 'callable' === $hint) {
                    $php .= $hint;
                } else {
                    $php .= '\\'.$hint;
                }
            }
            $php .= ' '.($argument->isPassedByReference() ? '&' : '').'$'.$argument->getName();
            if ($argument->isOptional()) {
                $php .= ' = '.var_export($argument->getDefault(), true);
            }
            return $php;
        }, $arguments);
    }
}
