<?php
namespace PhpSpec\CodeGenerator\Generator;
use PhpSpec\Locator\ResourceInterface;
class SpecificationGenerator extends PromptingGenerator
{
    public function supports(ResourceInterface $resource, $generation, array $data)
    {
        return 'specification' === $generation;
    }
    public function getPriority()
    {
        return 0;
    }
    protected function renderTemplate(ResourceInterface $resource, $filepath)
    {
        $values = array(
            '%filepath%'  => $filepath,
            '%name%'      => $resource->getSpecName(),
            '%namespace%' => $resource->getSpecNamespace(),
            '%subject%'   => $resource->getSrcClassname()
        );
        if (!$content = $this->getTemplateRenderer()->render('specification', $values)) {
            $content = $this->getTemplateRenderer()->renderString($this->getTemplate(), $values);
        }
        return $content;
    }
    protected function getTemplate()
    {
        return file_get_contents(__DIR__.'/templates/specification.template');
    }
    protected function getFilePath(ResourceInterface $resource)
    {
        return $resource->getSpecFilename();
    }
    protected function getGeneratedMessage(ResourceInterface $resource, $filepath)
    {
        return sprintf(
            "<info>Specification for <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(),
            $filepath
        );
    }
}
