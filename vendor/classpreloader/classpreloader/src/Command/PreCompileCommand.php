<?php
namespace ClassPreloader\Command;
use ClassPreloader\Config;
use ClassPreloader\Exception\SkipFileException;
use ClassPreloader\Parser\DirVisitor;
use ClassPreloader\Parser\FileVisitor;
use ClassPreloader\Parser\NodeTraverser;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
class PreCompileCommand extends Command
{
    protected $printer;
    protected $parser;
    protected $input;
    protected $output;
    protected $traverser;
    public function __construct()
    {
        parent::__construct();
        $this->printer = new PrettyPrinter();
        $this->parser = new Parser(new Lexer());
    }
    protected function configure()
    {
        parent::configure();
        $this->setName('compile')
            ->setDescription('Compiles classes into a single file')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'CSV of filenames to load, or the path to a PHP script that returns an array of file names')
            ->addOption('output', null, InputOption::VALUE_REQUIRED)
            ->addOption('skip_dir_file', null, InputOption::VALUE_NONE, 'Skip files with __DIR__ or __FILE__ to make the cache portable')
            ->addOption('fix_dir', null, InputOption::VALUE_REQUIRED, 'Convert __DIR__ constants to the original directory of a file', 1)
            ->addOption('fix_file', null, InputOption::VALUE_REQUIRED, 'Convert __FILE__ constants to the original path of a file', 1)
            ->addOption('strip_comments', null, InputOption::VALUE_REQUIRED, 'Set to 1 to strip comments from each source file', 0)
            ->setHelp(<<<EOF
The <info>%command.name%</info> command iterates over each script, normalizes
the file to be wrapped in namespaces, and combines each file into a single PHP
file.
EOF
        );
    }
    protected function getTraverser()
    {
        if (!$this->traverser) {
            $this->traverser = new NodeTraverser();
            if ($this->input->getOption('fix_dir')) {
                $this->traverser->addVisitor(new DirVisitor($this->input->getOption('skip_dir_file')));
            }
            if ($this->input->getOption('fix_file')) {
                $this->traverser->addVisitor(new FileVisitor($this->input->getOption('skip_dir_file')));
            }
        }
        return $this->traverser;
    }
    protected function getCode($file)
    {
        if (!is_readable($file)) {
            throw new \RuntimeException("Cannot open {$file} for reading");
        }
        if ($this->input->getOption('strip_comments')) {
            $content = php_strip_whitespace($file);
        } else {
            $content = file_get_contents($file);
        }
        $parsed = $this->parser->parse($content);
        $stmts = $this->getTraverser()->traverseFile($parsed, $file);
        $pretty = $this->printer->prettyPrint($stmts);
        if (substr($pretty, 5) === '<?php') {
            $pretty = substr($pretty, 7);
        }
        if (strpos($pretty, 'namespace ') === false) {
            $pretty = "namespace {\n" . $pretty . "\n}\n";
        }
        return $pretty;
    }
    protected function validateCommand()
    {
        if (!$this->input->getOption('output')) {
            throw new \InvalidArgumentException('An output option is required');
        }
        if (!$this->input->getOption('config')) {
            throw new \InvalidArgumentException('A config option is required');
        }
    }
    protected function getFileList($config)
    {
        $this->output->writeln('> Loading configuration file');
        $filesystem = new Filesystem();
        if (strpos($config, ',')) {
            return array_filter(explode(',', $config));
        }
        if (!$filesystem->isAbsolutePath($config)) {
            $config = getcwd() . '/' . $config;
        }
        if (!file_exists($config)) {
            throw new \InvalidArgumentException(sprintf('Configuration file "%s" does not exist.', $config));
        }
        $result = require $config;
        if ($result instanceof Config) {
            foreach ($result->getVisitors() as $visitor) {
                $this->getTraverser()->addVisitor($visitor);
            }
            return $result;
        } elseif (is_array($result)) {
            return $result;
        }
        throw new \InvalidArgumentException('Config must return an array of filenames or a Config object');
    }
    protected function prepareOutput($outputFile)
    {
        $dir = dirname($outputFile);
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            throw new \RuntimeException('Unable to create directory ' . $dir);
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->validateCommand();
        $outputFile = $this->input->getOption('output');
        $config = $this->input->getOption('config');
        $files = $this->getFileList($config);
        $output->writeLn('- Found ' . count($files) . ' files');
        $this->prepareOutput($outputFile);
        if (!$handle = fopen($input->getOption('output'), 'w')) {
            throw new \RuntimeException("Unable to open {$outputFile} for writing");
        }
        fwrite($handle, "<?php\n");
        $output->writeln('> Compiling classes');
        $count = 0;
        $countSkipped = 0;
        foreach ($files as $file) {
            $count++;
            try {
                $code = $this->getCode($file);
                $this->output->writeln('- Writing ' . $file);
                fwrite($handle, $code . "\n");
            } catch (SkipFileException $ex) {
                $countSkipped++;
                $this->output->writeln('- Skipping ' . $file);
            }
        }
        fclose($handle);
        $output->writeln("> Compiled loader written to {$outputFile}");
        $output->writeln('- Files: ' . ($count - $countSkipped) . '/' . $count . ' (skipped: ' . $countSkipped . ')');
        $output->writeln('- Filesize: ' . (round(filesize($outputFile) / 1024)) . ' kb');
    }
}
