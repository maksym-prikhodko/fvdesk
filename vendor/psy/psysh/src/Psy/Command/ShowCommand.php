<?php
namespace Psy\Command;
use Psy\Exception\RuntimeException;
use Psy\Formatter\CodeFormatter;
use Psy\Formatter\SignatureFormatter;
use Psy\Output\ShellOutput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class ShowCommand extends ReflectingCommand
{
    protected function configure()
    {
        $this
            ->setName('show')
            ->setDefinition(array(
                new InputArgument('value', InputArgument::REQUIRED, 'Function, class, instance, constant, method or property to show.'),
            ))
            ->setDescription('Show the code for an object, class, constant, method or property.')
            ->setHelp(
                <<<HELP
Show the code for an object, class, constant, method or property.
e.g.
<return>>>> show \$myObject</return>
<return>>>> show Psy\Shell::debug</return>
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($value, $reflector) = $this->getTargetAndReflector($input->getArgument('value'));
        try {
            $output->page(CodeFormatter::format($reflector), ShellOutput::OUTPUT_RAW);
        } catch (RuntimeException $e) {
            $output->writeln(SignatureFormatter::format($reflector));
            throw $e;
        }
    }
}
