<?php namespace Illuminate\Mail;
use Aws\Ses\SesClient;
use Illuminate\Support\Manager;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Mail\Transport\MandrillTransport;
use Illuminate\Mail\Transport\SesTransport;
use Swift_SendmailTransport as SendmailTransport;
class TransportManager extends Manager {
	protected function createSmtpDriver()
	{
		$config = $this->app['config']['mail'];
		$transport = SmtpTransport::newInstance(
			$config['host'], $config['port']
		);
		if (isset($config['encryption']))
		{
			$transport->setEncryption($config['encryption']);
		}
		if (isset($config['username']))
		{
			$transport->setUsername($config['username']);
			$transport->setPassword($config['password']);
		}
		return $transport;
	}
	protected function createSendmailDriver()
	{
		$command = $this->app['config']['mail']['sendmail'];
		return SendmailTransport::newInstance($command);
	}
	protected function createSesDriver()
	{
		$sesClient = SesClient::factory($this->app['config']->get('services.ses', []));
		return new SesTransport($sesClient);
	}
	protected function createMailDriver()
	{
		return MailTransport::newInstance();
	}
	protected function createMailgunDriver()
	{
		$config = $this->app['config']->get('services.mailgun', array());
		return new MailgunTransport($config['secret'], $config['domain']);
	}
	protected function createMandrillDriver()
	{
		$config = $this->app['config']->get('services.mandrill', array());
		return new MandrillTransport($config['secret']);
	}
	protected function createLogDriver()
	{
		return new LogTransport($this->app->make('Psr\Log\LoggerInterface'));
	}
	public function getDefaultDriver()
	{
		return $this->app['config']['mail.driver'];
	}
	public function setDefaultDriver($name)
	{
		$this->app['config']['mail.driver'] = $name;
	}
}
