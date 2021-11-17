<?php namespace Illuminate\Database;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
class Seeder {
	protected $container;
	protected $command;
	public function run()
	{
	}
	public function call($class)
	{
		$this->resolve($class)->run();
		if (isset($this->command))
		{
			$this->command->getOutput()->writeln("<info>Seeded:</info> $class");
		}
	}
	protected function resolve($class)
	{
		if (isset($this->container))
		{
			$instance = $this->container->make($class);
			$instance->setContainer($this->container);
		}
		else
		{
			$instance = new $class;
		}
		if (isset($this->command))
		{
			$instance->setCommand($this->command);
		}
		return $instance;
	}
	public function setContainer(Container $container)
	{
		$this->container = $container;
		return $this;
	}
	public function setCommand(Command $command)
	{
		$this->command = $command;
		return $this;
	}
}
