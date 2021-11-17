<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class ModelMakeCommand extends GeneratorCommand {
	protected $name = 'make:model';
	protected $description = 'Create a new Eloquent model class';
	protected $type = 'Model';
	public function fire()
	{
		parent::fire();
		if ( ! $this->option('no-migration'))
		{
			$table = str_plural(snake_case(class_basename($this->argument('name'))));
			$this->call('make:migration', ['name' => "create_{$table}_table", '--create' => $table]);
		}
	}
	protected function getStub()
	{
		return __DIR__.'/stubs/model.stub';
	}
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace;
	}
	protected function getOptions()
	{
		return array(
			array('no-migration', null, InputOption::VALUE_NONE, 'Do not create a new migration file.'),
		);
	}
}
