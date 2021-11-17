<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class HandlerCommandCommand extends GeneratorCommand {
	protected $name = 'handler:command';
	protected $description = 'Create a new command handler class';
	protected $type = 'Handler';
	protected function buildClass($name)
	{
		$stub = parent::buildClass($name);
		$stub = str_replace(
			'{{command}}', class_basename($this->option('command')), $stub
		);
		$stub = str_replace(
			'{{fullCommand}}', $this->option('command'), $stub
		);
		return $stub;
	}
	protected function getStub()
	{
		return __DIR__.'/stubs/command-handler.stub';
	}
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace.'\Handlers\Commands';
	}
	protected function getOptions()
	{
		return array(
			array('command', null, InputOption::VALUE_REQUIRED, 'The command class the handler handles.'),
		);
	}
}
