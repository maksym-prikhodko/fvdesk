<?php
namespace PhpSpec\CodeGenerator\Generator;
use PhpSpec\Console\IO;
use PhpSpec\CodeGenerator\TemplateRenderer;
use PhpSpec\Util\Filesystem;
use PhpSpec\Locator\ResourceInterface;
abstract class PromptingGenerator implements GeneratorInterface
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
    public function generate(ResourceInterface $resource, array $data = array())
    {
        $filepath = $this->getFilePath($resource);
        if ($this->ifFileAlreadyExists($filepath)) {
            if ($this->userAborts($filepath)) {
                return;
            }
            $this->io->writeln();
        }
        $this->createDirectoryIfItDoesExist($filepath);
        $this->generateFileAndRenderTemplate($resource, $filepath);
    }
    protected function getTemplateRenderer()
    {
        return $this->templates;
    }
    abstract protected function getFilePath(ResourceInterface $resource);
    abstract protected function renderTemplate(ResourceInterface $resource, $filepath);
    abstract protected function getGeneratedMessage(ResourceInterface $resource, $filepath);
    private function ifFileAlreadyExists($filepath)
    {
        return $this->filesystem->pathExists($filepath);
    }
    private function userAborts($filepath)
    {
        $message = sprintf('File "%s" already exists. Overwrite?', basename($filepath));
        return !$this->io->askConfirmation($message, false);
    }
    private function createDirectoryIfItDoesExist($filepath)
    {
        $path = dirname($filepath);
        if (!$this->filesystem->isDirectory($path)) {
            $this->filesystem->makeDirectory($path);
        }
    }
    private function generateFileAndRenderTemplate(ResourceInterface $resource, $filepath)
    {
        $content = $this->renderTemplate($resource, $filepath);
        $this->filesystem->putFileContents($filepath, $content);
        $this->io->writeln($this->getGeneratedMessage($resource, $filepath));
    }
}
