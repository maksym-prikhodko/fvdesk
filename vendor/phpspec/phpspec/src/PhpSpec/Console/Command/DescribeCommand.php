<?php
namespace PhpSpec\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
class DescribeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('describe')
            ->setDefinition(array(
                    new InputArgument('class', InputArgument::REQUIRED, 'Class to describe'),
                ))
            ->setDescription('Creates a specification for a class')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a specification for a class:
  <info>php %command.full_name% ClassName</info>
Will generate a specification ClassNameSpec in the spec directory.
  <info>php %command.full_name% Namespace/ClassName</info>
Will generate a namespaced specification Namespace\ClassNameSpec.
Note that / is used as the separator. To use \ it must be quoted:
  <info>php %command.full_name% "Namespace\ClassName"</info>
EOF
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getContainer();
        $container->configure();
        $classname = $input->getArgument('class');
        $resource  = $container->get('locator.resource_manager')->createResource($classname);
        $container->get('code_generator')->generate($resource, 'specification');
    }
}
