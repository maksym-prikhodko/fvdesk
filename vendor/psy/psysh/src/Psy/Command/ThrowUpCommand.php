<?php
namespace Psy\Command;
use Psy\Context;
use Psy\ContextAware;
use Psy\Exception\ThrowUpException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class ThrowUpCommand extends Command implements ContextAware
{
    protected $context;
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
    protected function configure()
    {
        $this
            ->setName('throw-up')
            ->setDefinition(array(
                new InputArgument('exception', InputArgument::OPTIONAL, 'Exception to throw'),
            ))
            ->setDescription('Throw an exception out of the Psy Shell.')
            ->setHelp(
                <<<HELP
Throws an exception out of the current the Psy Shell instance.
By default it throws the most recent exception.
e.g.
<return>>>> throw-up</return>
<return>>>> throw-up \$e</return>
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($name = $input->getArgument('exception')) {
            $orig = $this->context->get(preg_replace('/^\$/', '', $name));
        } else {
            $orig = $this->context->getLastException();
        }
        if (!$orig instanceof \Exception) {
            throw new \InvalidArgumentException('throw-up can only throw Exceptions');
        }
        throw new ThrowUpException($orig);
    }
}
