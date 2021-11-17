<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class EventGenerateCommand extends Command {
	protected $name = 'event:generate';
	protected $description = 'Generate the missing events and handlers based on registration';
	public function fire()
	{
		$provider = $this->laravel->getProvider(
			'Illuminate\Foundation\Support\Providers\EventServiceProvider'
		);
		foreach ($provider->listens() as $event => $handlers)
		{
			if ( ! str_contains($event, '\\'))
				continue;
			$this->callSilent('make:event', ['name' => $event]);
			foreach ($handlers as $handler)
			{
				$this->callSilent('handler:event', ['name' => $handler, '--event' => $event]);
			}
		}
		$this->info('Events and handlers generated successfully!');
	}
}
