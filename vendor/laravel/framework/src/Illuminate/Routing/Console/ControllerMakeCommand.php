<?php namespace Illuminate\Routing\Console;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class ControllerMakeCommand extends GeneratorCommand {
	protected $name = 'make:controller';
	protected $description = 'Create a new resource controller class';
	protected $type = 'Controller';
	protected function getStub()
	{
		if ($this->option('plain'))
		{
			return __DIR__.'/stubs/controller.plain.stub';
		}
		return __DIR__.'/stubs/controller.stub';
	}
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace.'\Http\Controllers';
	}
	protected function getOptions()
	{
		return array(
			array('plain', null, InputOption::VALUE_NONE, 'Generate an empty controller class.'),
		);
	}
}
