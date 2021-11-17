<?php
namespace PhpSpec\Console\Prompter;
use PhpSpec\Console\Prompter;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
final class Question implements Prompter
{
    private $input;
    private $output;
    private $helper;
    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
    }
    public function askConfirmation($question, $default = true)
    {
        return (bool)$this->helper->ask($this->input, $this->output, new ConfirmationQuestion($question, $default));
    }
}
