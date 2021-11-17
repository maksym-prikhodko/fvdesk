<?php
namespace PhpSpec\CodeGenerator\Generator;
use PhpSpec\CodeGenerator\TemplateRenderer;
use PhpSpec\Console\IO;
use PhpSpec\Locator\ResourceInterface;
use PhpSpec\Util\Filesystem;
class ReturnConstantGenerator implements GeneratorInterface
{
    private $io;
    private $templates;
    private $filesystem;
    public function __construct(IO $io, TemplateRenderer $templates, Filesystem $filesystem = null)
    {
        $this->io = $io;
        $this->templates = $templates;
        $this->filesystem = $filesystem ?: new Filesystem();
    }
    public function supports(ResourceInterface $resource, $generation, array $data)
    {
        return 'returnConstant' == $generation;
    }
    public function generate(ResourceInterface $resource, array $data)
    {
        $method = $data['method'];
        $expected = $data['expected'];
        $code = $this->filesystem->getFileContents($resource->getSrcFilename());
        $values = array('%constant%' => var_export($expected, true));
        if (!$content = $this->templates->render('method', $values)) {
            $content = $this->templates->renderString(
                $this->getTemplate(),
                $values
            );
        }
        $pattern = '/'.'(function\s+'.preg_quote($method, '/').'\s*\([^\)]*\))\s+{[^}]*?}/';
        $replacement = '$1'.$content;
        $modifiedCode = preg_replace($pattern, $replacement, $code);
        $this->filesystem->putFileContents($resource->getSrcFilename(), $modifiedCode);
        $this->io->writeln(sprintf(
            "<info>Method <value>%s::%s()</value> has been modified.</info>\n",
            $resource->getSrcClassname(),
            $method
        ), 2);
    }
    public function getPriority()
    {
        return 0;
    }
    protected function getTemplate()
    {
        return file_get_contents(__DIR__.'/templates/returnconstant.template');
    }
}
