<?php namespace Illuminate\Console\Scheduling;
use Illuminate\Console\Command;
class ScheduleRunCommand extends Command {
	protected $name = 'schedule:run';
	protected $description = 'Run the scheduled commands';
	protected $schedule;
	public function __construct(Schedule $schedule)
	{
		$this->schedule = $schedule;
		parent::__construct();
	}
	public function fire()
	{
		$events = $this->schedule->dueEvents($this->laravel);
		foreach ($events as $event)
		{
			$this->line('<info>Running scheduled command:</info> '.$event->getSummaryForDisplay());
			$event->run($this->laravel);
		}
		if (count($events) === 0)
		{
			$this->info('No scheduled commands are ready to run.');
		}
	}
}
