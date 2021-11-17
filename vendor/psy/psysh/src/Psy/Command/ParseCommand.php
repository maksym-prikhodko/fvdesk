<?php
namespace Psy\Command;
use PhpParser\Lexer;
use PhpParser\Parser;
use Psy\Presenter\PHPParserPresenter;
use Psy\Presenter\PresenterManager;
use Psy\Presenter\PresenterManagerAware;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class ParseCommand extends Command implements PresenterManagerAware
{
    private $presenterManager;
    private $parser;
    public function setPresenterManager(PresenterManager $manager)
    {
        $this->presenterManager = new PresenterManager();
        foreach ($manager as $presenter) {
            $this->presenterManager->addPresenter($presenter);
        }
        $this->presenterManager->addPresenter(new PHPParserPresenter());
    }
    protected function configure()
    {
        $this
            ->setName('parse')
            ->setDefinition(array(
                new InputArgument('code', InputArgument::REQUIRED, 'PHP code to parse.'),
                new InputOption('depth', '', InputOption::VALUE_REQUIRED, 'Depth to parse', 10),
            ))
            ->setDescription('Parse PHP code and show the abstract syntax tree.')
            ->setHelp(
                <<<HELP
Parse PHP code and show the abstract syntax tree.
This command is used in the development of PsySH. Given a string of PHP code,
it pretty-prints the PHP Parser parse tree.
See https:
It prolly won't be super useful for most of you, but it's here if you want to play.
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        if (strpos('<?', $code) === false) {
            $code = '<?php ' . $code;
        }
        $depth = $input->getOption('depth');
        $nodes = $this->parse($code);
        $output->page($this->presenterManager->present($nodes, $depth));
    }
    private function parse($code)
    {
        $parser = $this->getParser();
        try {
            return $parser->parse($code);
        } catch (\PhpParser\Error $e) {
            if (strpos($e->getMessage(), 'unexpected EOF') === false) {
                throw $e;
            }
            return $parser->parse($code . ';');
        }
    }
    private function getParser()
    {
        if (!isset($this->parser)) {
            $this->parser = new Parser(new Lexer());
        }
        return $this->parser;
    }
}
