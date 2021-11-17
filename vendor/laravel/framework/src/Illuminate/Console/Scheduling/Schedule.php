<?php namespace Illuminate\Console\Scheduling;
use Illuminate\Contracts\Foundation\Application;
class Schedule {
	protected $events = [];
	public function call($callback, array $parameters = array())
	{
		$this->events[] = $event = new CallbackEvent($callback, $parameters);
		return $event;
	}
	public function command($command)
	{
		return $this->exec(PHP_BINARY.' artisan '.$command);
	}
	public function exec($command)
	{
		$this->events[] = $event = new Event($command);
		return $event;
	}
	public function events()
	{
		return $this->events;
	}
	public function dueEvents(Application $app)
	{
		return array_filter($this->events, function($event) use ($app)
		{
			return $event->isDue($app);
		});
	}
}
