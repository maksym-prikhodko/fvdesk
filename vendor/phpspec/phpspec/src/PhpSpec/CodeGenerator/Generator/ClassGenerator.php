<?php
namespace PhpSpec\CodeGenerator\Generator;
use PhpSpec\Locator\ResourceInterface;
class ClassGenerator extends PromptingGenerator
{
    public function supports(ResourceInterface $resource, $generation, array $data)
    {
        return 'class' === $generation;
    }
    public function getPriority()
    {
        return 0;
    }
    protected function renderTemplate(ResourceInterface $resource, $filepath)
    {
        $values = array(
            '%filepath%'        => $filepath,
            '%name%'            => $resource->getName(),
            '%namespace%'       => $resource->getSrcNamespace(),
            '%namespace_block%' => '' !== $resource->getSrcNamespace()
                                ?  sprintf("\n\nnamespace %s;", $resource->getSrcNamespace())
                                : '',
        );
        if (!$content = $this->getTemplateRenderer()->render('class', $values)) {
            $content = $this->getTemplateRenderer()->renderString(
                $this->getTemplate(),
                $values
            );
        }
        return $content;
    }
    protected function getTemplate()
    {
        return file_get_contents(__DIR__.'/templates/class.template');
    }
    protected function getFilePath(ResourceInterface $resource)
    {
        return $resource->getSrcFilename();
    }
    protected function getGeneratedMessage(ResourceInterface $resource, $filepath)
    {
        return sprintf(
            "<info>Class <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(),
            $filepath
        );
    }
}
