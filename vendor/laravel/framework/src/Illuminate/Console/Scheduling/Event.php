<?php namespace Illuminate\Console\Scheduling;
use Closure;
use Carbon\Carbon;
use LogicException;
use Cron\CronExpression;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Contracts\Mail\Mailer;
use Symfony\Component\Process\Process;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
class Event {
	public $command;
	public $expression = '* * * * * *';
	public $timezone;
	public $user;
	public $environments = [];
	public $evenInMaintenanceMode = false;
	public $withoutOverlapping = false;
	protected $filter;
	protected $reject;
	public $output = '/dev/null';
	protected $afterCallbacks = [];
	public $description;
	public function __construct($command)
	{
		$this->command = $command;
	}
	public function run(Container $container)
	{
		if (count($this->afterCallbacks) > 0)
		{
			$this->runCommandInForeground($container);
		}
		else
		{
			$this->runCommandInBackground();
		}
	}
	protected function runCommandInBackground()
	{
		chdir(base_path());
		exec($this->buildCommand());
	}
	protected function runCommandInForeground(Container $container)
	{
		(new Process(
			trim($this->buildCommand(), '& '), base_path(), null, null, null
		))->run();
		$this->callAfterCallbacks($container);
	}
	protected function callAfterCallbacks(Container $container)
	{
		foreach ($this->afterCallbacks as $callback)
		{
			$container->call($callback);
		}
	}
	public function buildCommand()
	{
		if ($this->withoutOverlapping)
		{
			$command = '(touch '.$this->mutexPath().'; '.$this->command.'; rm '.$this->mutexPath().') > '.$this->output.' 2>&1 &';
		}
		else
		{
			$command = $this->command.' > '.$this->output.' 2>&1 &';
		}
		return $this->user ? 'sudo -u '.$this->user.' '.$command : $command;
	}
	protected function mutexPath()
	{
		return storage_path().'/framework/schedule-'.md5($this->expression.$this->command);
	}
	public function isDue(Application $app)
	{
		if ( ! $this->runsInMaintenanceMode() && $app->isDownForMaintenance())
		{
			return false;
		}
		return $this->expressionPasses() &&
			   $this->filtersPass($app) &&
			   $this->runsInEnvironment($app->environment());
	}
	protected function expressionPasses()
	{
		$date = Carbon::now();
		if ($this->timezone)
		{
			$date->setTimezone($this->timezone);
		}
		return CronExpression::factory($this->expression)->isDue($date->toDateTimeString());
	}
	protected function filtersPass(Application $app)
	{
		if (($this->filter && ! $app->call($this->filter)) ||
			 $this->reject && $app->call($this->reject))
		{
			return false;
		}
		return true;
	}
	public function runsInEnvironment($environment)
	{
		return empty($this->environments) || in_array($environment, $this->environments);
	}
	public function runsInMaintenanceMode()
	{
		return $this->evenInMaintenanceMode;
	}
	public function cron($expression)
	{
		$this->expression = $expression;
		return $this;
	}
	public function hourly()
	{
		return $this->cron('0 * * * * *');
	}
	public function daily()
	{
		return $this->cron('0 0 * * * *');
	}
	public function at($time)
	{
		return $this->dailyAt($time);
	}
	public function dailyAt($time)
	{
		$segments = explode(':', $time);
		return $this->spliceIntoPosition(2, (int) $segments[0])
					->spliceIntoPosition(1, count($segments) == 2 ? (int) $segments[1] : '0');
	}
	public function twiceDaily()
	{
		return $this->cron('0 1,13 * * * *');
	}
	public function weekdays()
	{
		return $this->spliceIntoPosition(5, '1-5');
	}
	public function mondays()
	{
		return $this->days(1);
	}
	public function tuesdays()
	{
		return $this->days(2);
	}
	public function wednesdays()
	{
		return $this->days(3);
	}
	public function thursdays()
	{
		return $this->days(4);
	}
	public function fridays()
	{
		return $this->days(5);
	}
	public function saturdays()
	{
		return $this->days(6);
	}
	public function sundays()
	{
		return $this->days(0);
	}
	public function weekly()
	{
		return $this->cron('0 0 * * 0 *');
	}
	public function weeklyOn($day, $time = '0:0')
	{
		$this->dailyAt($time);
		return $this->spliceIntoPosition(5, $day);
	}
	public function monthly()
	{
		return $this->cron('0 0 1 * * *');
	}
	public function yearly()
	{
		return $this->cron('0 0 1 1 * *');
	}
	public function everyFiveMinutes()
	{
		return $this->cron('*/5 * * * * *');
	}
	public function everyTenMinutes()
	{
		return $this->cron('*/10 * * * * *');
	}
	public function everyThirtyMinutes()
	{
		return $this->cron('0,30 * * * * *');
	}
	public function days($days)
	{
		$days = is_array($days) ? $days : func_get_args();
		return $this->spliceIntoPosition(5, implode(',', $days));
	}
	public function timezone($timezone)
	{
		$this->timezone = $timezone;
		return $this;
	}
	public function user($user)
	{
		$this->user = $user;
		return $this;
	}
	public function environments($environments)
	{
		$this->environments = is_array($environments) ? $environments : func_get_args();
		return $this;
	}
	public function evenInMaintenanceMode()
	{
		$this->evenInMaintenanceMode = true;
		return $this;
	}
	public function withoutOverlapping()
	{
		$this->withoutOverlapping = true;
		return $this->skip(function()
		{
			return file_exists($this->mutexPath());
		});
	}
	public function when(Closure $callback)
	{
		$this->filter = $callback;
		return $this;
	}
	public function skip(Closure $callback)
	{
		$this->reject = $callback;
		return $this;
	}
	public function sendOutputTo($location)
	{
		$this->output = $location;
		return $this;
	}
	public function emailOutputTo($addresses)
	{
		if (is_null($this->output) || $this->output == '/dev/null')
		{
			throw new LogicException("Must direct output to a file in order to e-mail results.");
		}
		$addresses = is_array($addresses) ? $addresses : func_get_args();
		return $this->then(function(Mailer $mailer) use ($addresses)
		{
			$this->emailOutput($mailer, $addresses);
		});
	}
	protected function emailOutput(Mailer $mailer, $addresses)
	{
		$mailer->raw(file_get_contents($this->output), function($m) use ($addresses)
		{
			$m->subject($this->getEmailSubject());
			foreach ($addresses as $address)
			{
				$m->to($address);
			}
		});
	}
	protected function getEmailSubject()
	{
		if ($this->description)
		{
			return 'Scheduled Job Output ('.$this->description.')';
		}
		return 'Scheduled Job Output';
	}
	public function thenPing($url)
	{
		return $this->then(function() use ($url) { (new HttpClient)->get($url); });
	}
	public function then(Closure $callback)
	{
		$this->afterCallbacks[] = $callback;
		return $this;
	}
	public function name($description)
	{
		return $this->description($description);
	}
	public function description($description)
	{
		$this->description = $description;
		return $this;
	}
	protected function spliceIntoPosition($position, $value)
	{
		$segments = explode(' ', $this->expression);
		$segments[$position - 1] = $value;
		return $this->cron(implode(' ', $segments));
	}
	public function getSummaryForDisplay()
	{
		if (is_string($this->description)) return $this->description;
		return $this->buildCommand();
	}
	public function getExpression()
	{
		return $this->expression;
	}
}
