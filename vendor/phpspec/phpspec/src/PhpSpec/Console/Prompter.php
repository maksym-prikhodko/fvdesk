<?php
namespace PhpSpec\Console;
interface Prompter
{
    public function askConfirmation($question, $default = true);
}
