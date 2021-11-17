<?php
namespace Psy\Command;
use Psy\Exception\RuntimeException;
use Psy\Presenter\Presenter;
use Psy\Presenter\PresenterManager;
use Psy\Presenter\PresenterManagerAware;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class DumpCommand extends ReflectingCommand implements PresenterManagerAware
{
    private $presenterManager;
    public function setPresenterManager(PresenterManager $manager)
    {
        $this->presenterManager = $manager;
    }
    protected function configure()
    {
        $this
            ->setName('dump')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::REQUIRED, 'A target object or primitive to dump.', null),
                new InputOption('depth', '', InputOption::VALUE_REQUIRED, 'Depth to parse', 10),
                new InputOption('all', 'a', InputOption::VALUE_NONE, 'Include private and protected methods and properties.'),
            ))
            ->setDescription('Dump an object or primitive.')
            ->setHelp(
                <<<HELP
Dump an object or primitive.
This is like var_dump but <strong>way</strong> awesomer.
e.g.
<return>>>> dump \$_</return>
<return>>>> dump \$someVar</return>
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $depth  = $input->getOption('depth');
        $target = $this->resolveTarget($input->getArgument('target'));
        $output->page($this->presenterManager->present($target, $depth, $input->getOption('all') ? Presenter::VERBOSE : 0));
    }
    protected function resolveTarget($target)
    {
        $matches = array();
        if (preg_match(self::INSTANCE, $target, $matches)) {
            return $this->getScopeVariable($matches[1]);
        } else {
            throw new RuntimeException('Unknown target: ' . $target);
        }
    }
}
