<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
class HandlerEventCommand extends GeneratorCommand {
	protected $name = 'handler:event';
	protected $description = 'Create a new event handler class';
	protected $type = 'Handler';
	protected function buildClass($name)
	{
		$stub = parent::buildClass($name);
		$event = $this->option('event');
		if ( ! starts_with($event, $this->getAppNamespace()))
		{
			$event = $this->getAppNamespace().'Events\\'.$event;
		}
		$stub = str_replace(
			'{{event}}', class_basename($event), $stub
		);
		$stub = str_replace(
			'{{fullEvent}}', $event, $stub
		);
		return $stub;
	}
	protected function getStub()
	{
		if ($this->option('queued'))
		{
			return __DIR__.'/stubs/event-handler-queued.stub';
		}
		else
		{
			return __DIR__.'/stubs/event-handler.stub';
		}
	}
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace.'\Handlers\Events';
	}
	protected function getOptions()
	{
		return array(
			array('event', null, InputOption::VALUE_REQUIRED, 'The event class the handler handles.'),
			array('queued', null, InputOption::VALUE_NONE, 'Indicates the event handler should be queued.'),
		);
	}
}
