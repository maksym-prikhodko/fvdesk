<?php namespace Illuminate\Mail;
use Swift_Mailer;
use Illuminate\Support\ServiceProvider;
class MailServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->app->singleton('mailer', function($app)
		{
			$this->registerSwiftMailer();
			$mailer = new Mailer(
				$app['view'], $app['swift.mailer'], $app['events']
			);
			$this->setMailerDependencies($mailer, $app);
			$from = $app['config']['mail.from'];
			if (is_array($from) && isset($from['address']))
			{
				$mailer->alwaysFrom($from['address'], $from['name']);
			}
			$pretend = $app['config']->get('mail.pretend', false);
			$mailer->pretend($pretend);
			return $mailer;
		});
	}
	protected function setMailerDependencies($mailer, $app)
	{
		$mailer->setContainer($app);
		if ($app->bound('Psr\Log\LoggerInterface'))
		{
			$mailer->setLogger($app->make('Psr\Log\LoggerInterface'));
		}
		if ($app->bound('queue'))
		{
			$mailer->setQueue($app['queue.connection']);
		}
	}
	public function registerSwiftMailer()
	{
		$this->registerSwiftTransport();
		$this->app['swift.mailer'] = $this->app->share(function($app)
		{
			return new Swift_Mailer($app['swift.transport']->driver());
		});
	}
	protected function registerSwiftTransport()
	{
		$this->app['swift.transport'] = $this->app->share(function($app)
		{
			return new TransportManager($app);
		});
	}
	public function provides()
	{
		return ['mailer', 'swift.mailer', 'swift.transport'];
	}
}
