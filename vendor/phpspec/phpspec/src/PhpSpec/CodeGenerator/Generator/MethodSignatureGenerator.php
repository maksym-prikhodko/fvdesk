<?php
namespace PhpSpec\CodeGenerator\Generator;
use PhpSpec\Console\IO;
use PhpSpec\CodeGenerator\TemplateRenderer;
use PhpSpec\Util\Filesystem;
use PhpSpec\Locator\ResourceInterface;
class MethodSignatureGenerator implements GeneratorInterface
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
        return 'method-signature' === $generation;
    }
    public function generate(ResourceInterface $resource, array $data = array())
    {
        $filepath  = $resource->getSrcFilename();
        $name      = $data['name'];
        $arguments = $data['arguments'];
        $argString = $this->buildArgumentString($arguments);
        $values = array('%name%' => $name, '%arguments%' => $argString);
        if (!$content = $this->templates->render('interface-method-signature', $values)) {
            $content = $this->templates->renderString(
                $this->getTemplate(), $values
            );
        }
        $this->insertMethodSignature($filepath, $content);
        $this->io->writeln(sprintf(
            "<info>Method signature <value>%s::%s()</value> has been created.</info>\n",
            $resource->getSrcClassname(), $name
        ), 2);
    }
    public function getPriority()
    {
        return 0;
    }
    protected function getTemplate()
    {
        return file_get_contents(__DIR__.'/templates/interface_method_signature.template');
    }
    private function insertMethodSignature($filepath, $content)
    {
        $code = $this->filesystem->getFileContents($filepath);
        $code = preg_replace('/}[ \n]*$/', rtrim($content) . "\n}\n", trim($code));
        $this->filesystem->putFileContents($filepath, $code);
    }
    private function buildArgumentString($arguments)
    {
        $argString = count($arguments)
            ? '$argument' . implode(', $argument', range(1, count($arguments)))
            : '';
        return $argString;
    }
}
