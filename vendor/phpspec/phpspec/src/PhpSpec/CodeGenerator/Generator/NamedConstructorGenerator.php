<?php
namespace PhpSpec\CodeGenerator\Generator;
use PhpSpec\CodeGenerator\TemplateRenderer;
use PhpSpec\Console\IO;
use PhpSpec\Locator\ResourceInterface;
use PhpSpec\Util\Filesystem;
class NamedConstructorGenerator implements GeneratorInterface
{
    private $io;
    private $templates;
    private $filesystem;
    public function __construct(IO $io, TemplateRenderer $templates, Filesystem $filesystem = null)
    {
        $this->io         = $io;
        $this->templates  = $templates;
        $this->filesystem = $filesystem ?: new Filesystem();
    }
    public function supports(ResourceInterface $resource, $generation, array $data)
    {
        return 'named_constructor' === $generation;
    }
    public function generate(ResourceInterface $resource, array $data = array())
    {
        $filepath   = $resource->getSrcFilename();
        $methodName = $data['name'];
        $arguments  = $data['arguments'];
        $content = $this->getContent($resource, $methodName, $arguments);
        $code = $this->filesystem->getFileContents($filepath);
        $code = preg_replace('/}[ \n]*$/', rtrim($content)."\n}\n", trim($code));
        $this->filesystem->putFileContents($filepath, $code);
        $this->io->writeln(sprintf(
            "<info>Method <value>%s::%s()</value> has been created.</info>\n",
            $resource->getSrcClassname(),
            $methodName
        ), 2);
    }
    public function getPriority()
    {
        return 0;
    }
    private function getContent(ResourceInterface $resource, $methodName, $arguments)
    {
        $className = $resource->getName();
        $class = $resource->getSrcClassname();
        $template = new CreateObjectTemplate($this->templates, $methodName, $arguments, $className);
        if (method_exists($class, '__construct')) {
            $template = new ExistingConstructorTemplate(
                $this->templates,
                $methodName,
                $arguments,
                $className,
                $class
            );
        }
        return $template->getContent();
    }
}
