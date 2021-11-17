<?php
namespace PhpSpec\Console\Prompter;
use PhpSpec\Console\Prompter;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;
final class Dialog implements Prompter
{
    private $output;
    private $dialogHelper;
    public function __construct(OutputInterface $output, DialogHelper $dialogHelper)
    {
        $this->output = $output;
        $this->dialogHelper = $dialogHelper;
    }
    public function askConfirmation($question, $default = true)
    {
        return $this->dialogHelper->askConfirmation($this->output, $question, $default);
    }
}
