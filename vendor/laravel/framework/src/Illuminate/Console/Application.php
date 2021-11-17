<?php namespace Illuminate\Console;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Illuminate\Contracts\Console\Application as ApplicationContract;
class Application extends SymfonyApplication implements ApplicationContract {
	protected $laravel;
	protected $lastOutput;
	public function __construct(Container $laravel, Dispatcher $events, $version)
	{
		parent::__construct('Laravel Framework', $version);
		$this->laravel = $laravel;
		$this->setAutoExit(false);
		$this->setCatchExceptions(false);
		$events->fire('artisan.start', [$this]);
	}
	public function call($command, array $parameters = array())
	{
		$parameters['command'] = $command;
		$this->lastOutput = new BufferedOutput;
		return $this->find($command)->run(new ArrayInput($parameters), $this->lastOutput);
	}
	public function output()
	{
		return $this->lastOutput ? $this->lastOutput->fetch() : '';
	}
	public function add(SymfonyCommand $command)
	{
		if ($command instanceof Command)
		{
			$command->setLaravel($this->laravel);
		}
		return $this->addToParent($command);
	}
	protected function addToParent(SymfonyCommand $command)
	{
		return parent::add($command);
	}
	public function resolve($command)
	{
		return $this->add($this->laravel->make($command));
	}
	public function resolveCommands($commands)
	{
		$commands = is_array($commands) ? $commands : func_get_args();
		foreach ($commands as $command)
		{
			$this->resolve($command);
		}
		return $this;
	}
	protected function getDefaultInputDefinition()
	{
		$definition = parent::getDefaultInputDefinition();
		$definition->addOption($this->getEnvironmentOption());
		return $definition;
	}
	protected function getEnvironmentOption()
	{
		$message = 'The environment the command should run under.';
		return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
	}
	public function getLaravel()
	{
		return $this->laravel;
	}
}
