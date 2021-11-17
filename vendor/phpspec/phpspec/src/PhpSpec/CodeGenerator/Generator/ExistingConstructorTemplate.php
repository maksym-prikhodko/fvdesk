<?php
namespace PhpSpec\CodeGenerator\Generator;
use PhpSpec\CodeGenerator\TemplateRenderer;
use ReflectionMethod;
class ExistingConstructorTemplate
{
    private $templates;
    private $class;
    private $className;
    private $arguments;
    private $methodName;
    public function __construct(TemplateRenderer $templates, $methodName, array $arguments, $className, $class)
    {
        $this->templates  = $templates;
        $this->class      = $class;
        $this->className  = $className;
        $this->arguments  = $arguments;
        $this->methodName = $methodName;
    }
    public function getContent()
    {
        if (!$this->numberOfConstructorArgumentsMatchMethod()) {
            return $this->getExceptionContent();
        }
        return $this->getCreateObjectContent();
    }
    private function numberOfConstructorArgumentsMatchMethod()
    {
        $constructorArguments = 0;
        $constructor = new ReflectionMethod($this->class, '__construct');
        $params = $constructor->getParameters();
        foreach ($params as $param) {
            if (!$param->isOptional()) {
                $constructorArguments++;
            }
        }
        return $constructorArguments == count($this->arguments);
    }
    private function getExceptionContent()
    {
        $values = $this->getValues();
        if (!$content = $this->templates->render('named_constructor_exception', $values)) {
            $content = $this->templates->renderString(
                $this->getExceptionTemplate(),
                $values
            );
        }
        return $content;
    }
    private function getCreateObjectContent()
    {
        $values = $this->getValues(true);
        if (!$content = $this->templates->render('named_constructor_create_object', $values)) {
            $content = $this->templates->renderString(
                $this->getCreateObjectTemplate(),
                $values
            );
        }
        return $content;
    }
    private function getValues($constructorArguments = false)
    {
        $argString = count($this->arguments)
            ? '$argument'.implode(', $argument', range(1, count($this->arguments)))
            : ''
        ;
        return array(
            '%methodName%'           => $this->methodName,
            '%arguments%'            => $argString,
            '%returnVar%'            => '$'.lcfirst($this->className),
            '%className%'            => $this->className,
            '%constructorArguments%' => $constructorArguments ? $argString : ''
        );
    }
    private function getCreateObjectTemplate()
    {
        return file_get_contents(__DIR__.'/templates/named_constructor_create_object.template');
    }
    private function getExceptionTemplate()
    {
        return file_get_contents(__DIR__.'/templates/named_constructor_exception.template');
    }
}
