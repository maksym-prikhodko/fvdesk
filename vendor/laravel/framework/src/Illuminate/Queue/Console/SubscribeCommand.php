<?php namespace Illuminate\Queue\Console;
use Exception;
use RuntimeException;
use Illuminate\Queue\IronQueue;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
class SubscribeCommand extends Command {
	protected $name = 'queue:subscribe';
	protected $description = 'Subscribe a URL to an Iron.io push queue';
	protected $meta;
	public function fire()
	{
		$iron = $this->laravel['queue']->connection();
		if ( ! $iron instanceof IronQueue)
		{
			throw new RuntimeException("Iron.io based queue must be default.");
		}
		$iron->getIron()->updateQueue($this->argument('queue'), $this->getQueueOptions());
		$this->line('<info>Queue subscriber added:</info> <comment>'.$this->argument('url').'</comment>');
	}
	protected function getQueueOptions()
	{
		return array(
			'push_type' => $this->getPushType(), 'subscribers' => $this->getSubscriberList(),
		);
	}
	protected function getPushType()
	{
		if ($this->option('type')) return $this->option('type');
		try
		{
			return $this->getQueue()->push_type;
		}
		catch (Exception $e)
		{
			return 'multicast';
		}
	}
	protected function getSubscriberList()
	{
		$subscribers = $this->getCurrentSubscribers();
		$url = $this->argument('url');
		if ( ! starts_with($url, ['http:
		{
			$url = $this->laravel['url']->to($url);
		}
		$subscribers[] = array('url' => $url);
		return $subscribers;
	}
	protected function getCurrentSubscribers()
	{
		try
		{
			return $this->getQueue()->subscribers;
		}
		catch (Exception $e)
		{
			return array();
		}
	}
	protected function getQueue()
	{
		if (isset($this->meta)) return $this->meta;
		return $this->meta = $this->laravel['queue']->getIron()->getQueue($this->argument('queue'));
	}
	protected function getArguments()
	{
		return array(
			array('queue', InputArgument::REQUIRED, 'The name of Iron.io queue.'),
			array('url', InputArgument::REQUIRED, 'The URL to be subscribed.'),
		);
	}
	protected function getOptions()
	{
		return array(
			array('type', null, InputOption::VALUE_OPTIONAL, 'The push type for the queue.'),
		);
	}
}
