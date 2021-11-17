<?php
namespace Psy;
use Psy\Exception\BreakException;
use Psy\Exception\ErrorException;
use Psy\Exception\Exception as PsyException;
use Psy\Exception\ThrowUpException;
use Psy\Output\ShellOutput;
use Psy\Presenter\PresenterManagerAware;
use Psy\TabCompletion\Matcher;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
class Shell extends Application
{
    const VERSION = 'v0.4.4';
    const PROMPT      = '>>> ';
    const BUFF_PROMPT = '... ';
    const REPLAY      = '--> ';
    const RETVAL      = '=> ';
    private $config;
    private $cleaner;
    private $output;
    private $readline;
    private $inputBuffer;
    private $code;
    private $codeBuffer;
    private $codeBufferOpen;
    private $context;
    private $includes;
    private $loop;
    private $outputWantsNewline = false;
    private $completion;
    private $tabCompletionMatchers = array();
    public function __construct(Configuration $config = null)
    {
        $this->config   = $config ?: new Configuration();
        $this->cleaner  = $this->config->getCodeCleaner();
        $this->loop     = $this->config->getLoop();
        $this->context  = new Context();
        $this->includes = array();
        $this->readline = $this->config->getReadline();
        parent::__construct('Psy Shell', self::VERSION);
        $this->config->setShell($this);
        if ($this->config->getTabCompletion()) {
            $this->completion = $this->config->getAutoCompleter();
            $this->addTabCompletionMatchers($this->config->getTabCompletionMatchers());
            foreach ($this->getTabCompletionMatchers() as $matcher) {
                if ($matcher instanceof ContextAware) {
                    $matcher->setContext($this->context);
                }
                $this->completion->addMatcher($matcher);
            }
            $this->completion->activate();
        }
    }
    public static function isIncluded(array $trace)
    {
        return isset($trace[0]['function']) &&
          in_array($trace[0]['function'], array('require', 'include', 'require_once', 'include_once'));
    }
    public static function debug(array $vars = array(), $bind = null)
    {
        echo PHP_EOL;
        if ($bind !== null) {
            $vars['this'] = $bind;
        }
        $sh = new \Psy\Shell();
        $sh->setScopeVariables($vars);
        $sh->run();
        return $sh->getScopeVariables();
    }
    public function add(BaseCommand $command)
    {
        if ($ret = parent::add($command)) {
            if ($ret instanceof ContextAware) {
                $ret->setContext($this->context);
            }
            if ($ret instanceof PresenterManagerAware) {
                $ret->setPresenterManager($this->config->getPresenterManager());
            }
        }
        return $ret;
    }
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
        ));
    }
    protected function getDefaultCommands()
    {
        $hist = new Command\HistoryCommand();
        $hist->setReadline($this->readline);
        return array(
            new Command\HelpCommand(),
            new Command\ListCommand(),
            new Command\DumpCommand(),
            new Command\DocCommand(),
            new Command\ShowCommand(),
            new Command\WtfCommand(),
            new Command\WhereamiCommand(),
            new Command\ThrowUpCommand(),
            new Command\TraceCommand(),
            new Command\BufferCommand(),
            new Command\ClearCommand(),
            $hist,
            new Command\ExitCommand(),
        );
    }
    protected function getTabCompletionMatchers()
    {
        if (empty($this->tabCompletionMatchers)) {
            $this->tabCompletionMatchers = array(
                new Matcher\CommandsMatcher($this->all()),
                new Matcher\KeywordsMatcher(),
                new Matcher\VariablesMatcher(),
                new Matcher\ConstantsMatcher(),
                new Matcher\FunctionsMatcher(),
                new Matcher\ClassNamesMatcher(),
                new Matcher\ClassMethodsMatcher(),
                new Matcher\ClassAttributesMatcher(),
                new Matcher\ObjectMethodsMatcher(),
                new Matcher\ObjectAttributesMatcher(),
            );
        }
        return $this->tabCompletionMatchers;
    }
    public function addTabCompletionMatchers(array $matchers)
    {
        $this->tabCompletionMatchers = array_merge($matchers, $this->getTabCompletionMatchers());
    }
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($input === null && !isset($_SERVER['argv'])) {
            $input = new ArgvInput(array());
        }
        if ($output === null) {
            $output = $this->config->getOutput();
        }
        return parent::run($input, $output);
    }
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->setOutput($output);
        $this->resetCodeBuffer();
        $this->setAutoExit(false);
        $this->setCatchExceptions(true);
        $this->readline->readHistory();
        $this->output->writeln($this->getHeader());
        try {
            $this->loop->run($this);
        } catch (ThrowUpException $e) {
            $this->setCatchExceptions(false);
            throw $e->getPrevious();
        }
    }
    public function getInput()
    {
        $this->codeBufferOpen = false;
        do {
            $this->output->setVerbosity(ShellOutput::VERBOSITY_VERBOSE);
            $input = $this->readline();
            if ($input === false) {
                $this->output->writeln('');
                if ($this->hasCode()) {
                    $this->resetCodeBuffer();
                } else {
                    throw new BreakException('Ctrl+D');
                }
            }
            if (trim($input) === '') {
                continue;
            }
            if ($this->hasCommand($input)) {
                $this->readline->addHistory($input);
                $this->runCommand($input);
                continue;
            }
            $this->addCode($input);
        } while (!$this->hasValidCode());
    }
    public function beforeLoop()
    {
        $this->loop->beforeLoop();
    }
    public function afterLoop()
    {
        $this->loop->afterLoop();
    }
    public function setScopeVariables(array $vars)
    {
        $this->context->setAll($vars);
    }
    public function getScopeVariables()
    {
        return $this->context->getAll();
    }
    public function getScopeVariableNames()
    {
        return array_keys($this->context->getAll());
    }
    public function getScopeVariable($name)
    {
        return $this->context->get($name);
    }
    public function setIncludes(array $includes = array())
    {
        $this->includes = $includes;
    }
    public function getIncludes()
    {
        return array_merge($this->config->getDefaultIncludes(), $this->includes);
    }
    public function hasCode()
    {
        return !empty($this->codeBuffer);
    }
    protected function hasValidCode()
    {
        return !$this->codeBufferOpen && $this->code !== false;
    }
    public function addCode($code)
    {
        try {
            if (substr(rtrim($code), -1) === '\\') {
                $this->codeBufferOpen = true;
                $code = substr(rtrim($code), 0, -1);
            } else {
                $this->codeBufferOpen = false;
            }
            $this->codeBuffer[] = $code;
            $this->code         = $this->cleaner->clean($this->codeBuffer, $this->config->requireSemicolons());
        } catch (\Exception $e) {
            $this->readline->addHistory(implode("\n", $this->codeBuffer));
            throw $e;
        }
    }
    public function getCodeBuffer()
    {
        return $this->codeBuffer;
    }
    protected function runCommand($input)
    {
        $command = $this->getCommand($input);
        if (empty($command)) {
            throw new \InvalidArgumentException('Command not found: ' . $input);
        }
        $input = new StringInput(str_replace('\\', '\\\\', rtrim($input, " \t\n\r\0\x0B;")));
        if ($input->hasParameterOption(array('--help', '-h'))) {
            $helpCommand = $this->get('help');
            $helpCommand->setCommand($command);
            return $helpCommand->run($input, $this->output);
        }
        return $command->run($input, $this->output);
    }
    public function resetCodeBuffer()
    {
        $this->codeBuffer = array();
        $this->code       = false;
    }
    public function addInput($input)
    {
        foreach ((array) $input as $line) {
            $this->inputBuffer[] = $line;
        }
    }
    public function flushCode()
    {
        if ($this->hasValidCode()) {
            $this->readline->addHistory(implode("\n", $this->codeBuffer));
            $code = $this->code;
            $this->resetCodeBuffer();
            return $code;
        }
    }
    public function getNamespace()
    {
        if ($namespace = $this->cleaner->getNamespace()) {
            return implode('\\', $namespace);
        }
    }
    public function writeStdout($out, $phase = PHP_OUTPUT_HANDLER_END)
    {
        $isCleaning = false;
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $isCleaning = $phase & PHP_OUTPUT_HANDLER_CLEAN;
        }
        if (!empty($out) && !$isCleaning) {
            $this->output->write($out, false, ShellOutput::OUTPUT_RAW);
            $this->outputWantsNewline = (substr($out, -1) !== "\n");
        }
        if ($this->outputWantsNewline && $phase & PHP_OUTPUT_HANDLER_END) {
            $this->output->writeln('<aside>⏎</aside>');
            $this->outputWantsNewline = false;
        }
    }
    public function writeReturnValue($ret)
    {
        $this->context->setReturnValue($ret);
        $ret    = $this->presentValue($ret);
        $indent = str_repeat(' ', strlen(self::RETVAL));
        $this->output->writeln(self::RETVAL . str_replace(PHP_EOL, PHP_EOL . $indent, $ret));
    }
    public function writeException(\Exception $e)
    {
        $this->renderException($e, $this->output);
    }
    public function renderException($e, $output)
    {
        $this->context->setLastException($e);
        $message = $e->getMessage();
        if (!$e instanceof PsyException) {
            $message = sprintf('%s with message \'%s\'', get_class($e), $message);
        }
        $severity = ($e instanceof \ErrorException) ? $this->getSeverity($e) : 'error';
        $output->writeln(sprintf('<%s>%s</%s>', $severity, OutputFormatter::escape($message), $severity));
        $this->resetCodeBuffer();
    }
    protected function getSeverity(\ErrorException $e)
    {
        $severity = $e->getSeverity();
        if ($severity & error_reporting()) {
            switch ($severity) {
                case E_WARNING:
                case E_NOTICE:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                case E_USER_WARNING:
                case E_USER_NOTICE:
                case E_STRICT:
                    return 'warning';
                default:
                    return 'error';
            }
        } else {
            return 'warning';
        }
    }
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if ($errno & error_reporting()) {
            ErrorException::throwException($errno, $errstr, $errfile, $errline);
        } else {
            $this->writeException(new ErrorException($errstr, 0, $errno, $errfile, $errline));
        }
    }
    protected function presentValue($val)
    {
        return $this->config->getPresenterManager()->present($val);
    }
    protected function getCommand($input)
    {
        $input = new StringInput($input);
        if ($name = $input->getFirstArgument()) {
            return $this->get($name);
        }
    }
    protected function hasCommand($input)
    {
        $input = new StringInput($input);
        if ($name = $input->getFirstArgument()) {
            return $this->has($name);
        }
        return false;
    }
    protected function getPrompt()
    {
        return $this->hasCode() ? self::BUFF_PROMPT : self::PROMPT;
    }
    protected function readline()
    {
        if (!empty($this->inputBuffer)) {
            $line = array_shift($this->inputBuffer);
            $this->output->writeln(sprintf('<aside>%s %s</aside>', self::REPLAY, OutputFormatter::escape($line)));
            return $line;
        }
        return $this->readline->readline($this->getPrompt());
    }
    protected function getHeader()
    {
        return sprintf("<aside>%s by Justin Hileman</aside>", $this->getVersion());
    }
    public function getVersion()
    {
        return sprintf("Psy Shell %s (PHP %s — %s)", self::VERSION, phpversion(), php_sapi_name());
    }
    public function getManualDb()
    {
        return $this->config->getManualDb();
    }
    protected function autocomplete($text)
    {
        $info = readline_info();
        $firstChar = substr($info['line_buffer'], max(0, $info['end'] - strlen($text) - 1), 1);
        if ($firstChar === '$') {
            return $this->getScopeVariableNames();
        }
    }
}
