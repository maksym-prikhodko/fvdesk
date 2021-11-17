<?php
namespace PhpSpec\CodeGenerator\Generator;
use PhpSpec\CodeGenerator\TemplateRenderer;
class CreateObjectTemplate
{
    private $templates;
    private $methodName;
    private $arguments;
    private $className;
    public function __construct(TemplateRenderer $templates, $methodName, $arguments, $className)
    {
        $this->templates  = $templates;
        $this->methodName = $methodName;
        $this->arguments  = $arguments;
        $this->className  = $className;
    }
    public function getContent()
    {
        $values = $this->getValues();
        if (!$content = $this->templates->render('named_constructor_create_object', $values)) {
            $content = $this->templates->renderString(
                $this->getTemplate(),
                $values
            );
        }
        return $content;
    }
    private function getTemplate()
    {
        return file_get_contents(__DIR__.'/templates/named_constructor_create_object.template');
    }
    private function getValues()
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
            '%constructorArguments%' => ''
        );
    }
}