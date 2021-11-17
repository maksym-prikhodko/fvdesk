<?php namespace Illuminate\Console;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Illuminate\Contracts\Foundation\Application as LaravelApplication;
class Command extends \Symfony\Component\Console\Command\Command {
	protected $laravel;
	protected $input;
	protected $output;
	protected $name;
	protected $description;
	public function __construct()
	{
		parent::__construct($this->name);
		$this->setDescription($this->description);
		$this->specifyParameters();
	}
	protected function specifyParameters()
	{
		foreach ($this->getArguments() as $arguments)
		{
			call_user_func_array(array($this, 'addArgument'), $arguments);
		}
		foreach ($this->getOptions() as $options)
		{
			call_user_func_array(array($this, 'addOption'), $options);
		}
	}
	public function run(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		$this->output = $output;
		return parent::run($input, $output);
	}
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$method = method_exists($this, 'handle') ? 'handle' : 'fire';
		return $this->laravel->call([$this, $method]);
	}
	public function call($command, array $arguments = array())
	{
		$instance = $this->getApplication()->find($command);
		$arguments['command'] = $command;
		return $instance->run(new ArrayInput($arguments), $this->output);
	}
	public function callSilent($command, array $arguments = array())
	{
		$instance = $this->getApplication()->find($command);
		$arguments['command'] = $command;
		return $instance->run(new ArrayInput($arguments), new NullOutput);
	}
	public function argument($key = null)
	{
		if (is_null($key)) return $this->input->getArguments();
		return $this->input->getArgument($key);
	}
	public function option($key = null)
	{
		if (is_null($key)) return $this->input->getOptions();
		return $this->input->getOption($key);
	}
	public function confirm($question, $default = false)
	{
		$helper = $this->getHelperSet()->get('question');
		$question = new ConfirmationQuestion("<question>{$question}</question> ", $default);
		return $helper->ask($this->input, $this->output, $question);
	}
	public function ask($question, $default = null)
	{
		$helper = $this->getHelperSet()->get('question');
		$question = new Question("<question>$question</question> ", $default);
		return $helper->ask($this->input, $this->output, $question);
	}
	public function askWithCompletion($question, array $choices, $default = null)
	{
		$helper = $this->getHelperSet()->get('question');
		$question = new Question("<question>$question</question> ", $default);
		$question->setAutocompleterValues($choices);
		return $helper->ask($this->input, $this->output, $question);
	}
	public function secret($question, $fallback = true)
	{
		$helper = $this->getHelperSet()->get('question');
		$question = new Question("<question>$question</question> ");
		$question->setHidden(true)->setHiddenFallback($fallback);
		return $helper->ask($this->input, $this->output, $question);
	}
	public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
	{
		$helper = $this->getHelperSet()->get('question');
		$question = new ChoiceQuestion("<question>$question</question> ", $choices, $default);
		$question->setMaxAttempts($attempts)->setMultiselect($multiple);
		return $helper->ask($this->input, $this->output, $question);
	}
	public function table(array $headers, array $rows, $style = 'default')
	{
		$table = new Table($this->output);
		$table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
	}
	public function info($string)
	{
		$this->output->writeln("<info>$string</info>");
	}
	public function line($string)
	{
		$this->output->writeln($string);
	}
	public function comment($string)
	{
		$this->output->writeln("<comment>$string</comment>");
	}
	public function question($string)
	{
		$this->output->writeln("<question>$string</question>");
	}
	public function error($string)
	{
		$this->output->writeln("<error>$string</error>");
	}
	protected function getArguments()
	{
		return array();
	}
	protected function getOptions()
	{
		return array();
	}
	public function getOutput()
	{
		return $this->output;
	}
	public function getLaravel()
	{
		return $this->laravel;
	}
	public function setLaravel(LaravelApplication $laravel)
	{
		$this->laravel = $laravel;
	}
}
