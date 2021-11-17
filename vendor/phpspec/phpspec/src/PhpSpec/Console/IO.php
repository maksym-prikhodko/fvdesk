<?php
namespace PhpSpec\Console;
use PhpSpec\IO\IOInterface;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpSpec\Config\OptionsConfig;
use Symfony\Component\Console\Question\ConfirmationQuestion;
class IO implements IOInterface
{
    const COL_MIN_WIDTH = 40;
    const COL_DEFAULT_WIDTH = 60;
    const COL_MAX_WIDTH = 80;
    private $input;
    private $output;
    private $lastMessage;
    private $hasTempString = false;
    private $config;
    private $consoleWidth;
    private $prompter;
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        OptionsConfig $config,
        Prompter $prompter
    ) {
        $this->input   = $input;
        $this->output  = $output;
        $this->config  = $config;
        $this->prompter = $prompter;
    }
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }
    public function isCodeGenerationEnabled()
    {
        if (!$this->isInteractive()) {
            return false;
        }
        return $this->config->isCodeGenerationEnabled()
            && !$this->input->getOption('no-code-generation');
    }
    public function isStopOnFailureEnabled()
    {
        return $this->config->isStopOnFailureEnabled()
            || $this->input->getOption('stop-on-failure');
    }
    public function isVerbose()
    {
        return OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity();
    }
    public function getLastWrittenMessage()
    {
        return $this->lastMessage;
    }
    public function writeln($message = '', $indent = null)
    {
        $this->write($message, $indent, true);
    }
    public function writeTemp($message, $indent = null)
    {
        $this->write($message, $indent);
        $this->hasTempString = true;
    }
    public function cutTemp()
    {
        if (false === $this->hasTempString) {
            return;
        }
        $message = $this->lastMessage;
        $this->write('');
        return $message;
    }
    public function freezeTemp()
    {
        $this->write($this->lastMessage);
    }
    public function write($message, $indent = null, $newline = false)
    {
        if ($this->hasTempString) {
            $this->hasTempString = false;
            $this->overwrite($message, $indent, $newline);
            return;
        }
        if (null !== $indent) {
            $message = $this->indentText($message, $indent);
        }
        $this->output->write($message, $newline);
        $this->lastMessage = $message.($newline ? "\n" : '');
    }
    public function overwriteln($message = '', $indent = null)
    {
        $this->overwrite($message, $indent, true);
    }
    public function overwrite($message, $indent = null, $newline = false)
    {
        if (null !== $indent) {
            $message = $this->indentText($message, $indent);
        }
        if ($message === $this->lastMessage) {
            return;
        }
        $commonPrefix = $this->getCommonPrefix($message, $this->lastMessage);
        $newSuffix = substr($message, strlen($commonPrefix));
        $oldSuffix = substr($this->lastMessage, strlen($commonPrefix));
        $overwriteLength = strlen(strip_tags($oldSuffix));
        $this->write(str_repeat("\x08", $overwriteLength));
        $this->write($newSuffix);
        $fill = $overwriteLength - strlen(strip_tags($newSuffix));
        if ($fill > 0) {
            $this->write(str_repeat(' ', $fill));
            $this->write(str_repeat("\x08", $fill));
        }
        if ($newline) {
            $this->writeln();
        }
        $this->lastMessage = $message.($newline ? "\n" : '');
    }
    private function getCommonPrefix($stringA, $stringB)
    {
        for ($i = 0, $len = min(strlen($stringA), strlen($stringB)); $i<$len; $i++) {
            if ($stringA[$i] != $stringB[$i]) {
                break;
            }
        }
        $common = substr($stringA, 0, $i);
        if (preg_match('/(^.*)<[a-z-]*>?[^<]*$/', $common, $matches)) {
            $common = $matches[1];
        }
        return $common;
    }
    public function askConfirmation($question, $default = true)
    {
        $lines   = array();
        $lines[] = '<question>'.str_repeat(' ', $this->getBlockWidth())."</question>";
        foreach (explode("\n", wordwrap($question, $this->getBlockWidth() - 4, "\n", true)) as $line) {
            $lines[] = '<question>  '.str_pad($line, $this->getBlockWidth() - 2).'</question>';
        }
        $lines[] = '<question>'.str_repeat(' ', $this->getBlockWidth() - 8).'</question> <value>'.
            ($default ? '[Y/n]' : '[y/N]').'</value> ';
        $formattedQuestion = implode("\n", $lines) . "\n";
        return $this->prompter->askConfirmation($formattedQuestion, $default);
    }
    private function indentText($text, $indent)
    {
        return implode("\n", array_map(
            function ($line) use ($indent) {
                return str_repeat(' ', $indent).$line;
            },
            explode("\n", $text)
        ));
    }
    public function isRerunEnabled()
    {
        return !$this->input->getOption('no-rerun') && $this->config->isReRunEnabled();
    }
    public function isFakingEnabled()
    {
        return $this->input->getOption('fake') || $this->config->isFakingEnabled();
    }
    public function getBootstrapPath()
    {
        if ($path = $this->input->getOption('bootstrap')) {
            return $path;
        }
        if ($path = $this->config->getBootstrapPath()) {
            return $path;
        }
        return false;
    }
    public function setConsoleWidth($width)
    {
        $this->consoleWidth = $width;
    }
    public function getBlockWidth()
    {
        $width = self::COL_DEFAULT_WIDTH;
        if ($this->consoleWidth && ($this->consoleWidth - 10) > self::COL_MIN_WIDTH) {
            $width = $this->consoleWidth - 10;
        }
        if ($width > self::COL_MAX_WIDTH) {
            $width = self::COL_MAX_WIDTH;
        }
        return $width;
    }
}
