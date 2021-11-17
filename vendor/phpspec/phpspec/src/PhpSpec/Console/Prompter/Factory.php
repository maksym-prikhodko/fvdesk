<?php
namespace PhpSpec\Console\Prompter;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
final class Factory
{
    private $helperSet;
    private $input;
    private $output;
    public function __construct(InputInterface $input, OutputInterface $output, HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
        $this->input = $input;
        $this->output = $output;
    }
    public function getPrompter()
    {
        if ($this->helperSet->has('question')) {
            return new Question($this->input, $this->output, $this->helperSet->get('question'));
        }
        return new Dialog($this->output, $this->helperSet->get('dialog'));
    }
}
