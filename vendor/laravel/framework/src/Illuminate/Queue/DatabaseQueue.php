<?php namespace Illuminate\Queue;
use DateTime;
use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Database\Query\Expression;
use Illuminate\Contracts\Queue\Queue as QueueContract;
class DatabaseQueue extends Queue implements QueueContract {
	protected $database;
	protected $table;
	protected $default;
	protected $expire = 60;
	public function __construct(Connection $database, $table, $default = 'default', $expire = 60)
	{
		$this->table = $table;
		$this->expire = $expire;
		$this->default = $default;
		$this->database = $database;
	}
	public function push($job, $data = '', $queue = null)
	{
		return $this->pushToDatabase(0, $queue, $this->createPayload($job, $data));
	}
	public function pushRaw($payload, $queue = null, array $options = array())
	{
		return $this->pushToDatabase(0, $queue, $payload);
	}
	public function later($delay, $job, $data = '', $queue = null)
	{
		return $this->pushToDatabase($delay, $queue, $this->createPayload($job, $data));
	}
	public function release($queue, $job, $delay)
	{
		return $this->pushToDatabase($delay, $queue, $job->payload, $job->attempts);
	}
	protected function pushToDatabase($delay, $queue, $payload, $attempts = 0)
	{
		$availableAt = $delay instanceof DateTime ? $delay : Carbon::now()->addSeconds($delay);
		return $this->database->table($this->table)->insertGetId([
			'queue' => $this->getQueue($queue),
			'payload' => $payload,
			'attempts' => $attempts,
			'reserved' => 0,
			'reserved_at' => null,
			'available_at' => $availableAt->getTimestamp(),
			'created_at' => $this->getTime(),
		]);
	}
	public function pop($queue = null)
	{
		$queue = $this->getQueue($queue);
		if ( ! is_null($this->expire))
		{
			$this->releaseJobsThatHaveBeenReservedTooLong($queue);
		}
		if ($job = $this->getNextAvailableJob($queue))
		{
			$this->markJobAsReserved($job->id);
			$this->database->commit();
			return new DatabaseJob(
				$this->container, $this, $job, $queue
			);
		}
		$this->database->commit();
	}
	protected function releaseJobsThatHaveBeenReservedTooLong($queue)
	{
		$expired = Carbon::now()->subSeconds($this->expire)->getTimestamp();
		$this->database->table($this->table)
					->where('queue', $this->getQueue($queue))
					->where('reserved', 1)
					->where('reserved_at', '<=', $expired)
					->update([
						'reserved' => 0,
						'reserved_at' => null,
						'attempts' => new Expression('attempts + 1'),
					]);
	}
	protected function getNextAvailableJob($queue)
	{
		$this->database->beginTransaction();
		$job = $this->database->table($this->table)
					->lockForUpdate()
					->where('queue', $this->getQueue($queue))
					->where('reserved', 0)
					->where('available_at', '<=', $this->getTime())
					->orderBy('id', 'asc')
					->first();
		return $job ? (object) $job : null;
	}
	protected function markJobAsReserved($id)
	{
		$this->database->table($this->table)->where('id', $id)->update([
			'reserved' => 1, 'reserved_at' => $this->getTime(),
		]);
	}
	public function deleteReserved($queue, $id)
	{
		$this->database->table($this->table)->where('id', $id)->delete();
	}
	protected function getQueue($queue)
	{
		return $queue ?: $this->default;
	}
	public function getDatabase()
	{
		return $this->database;
	}
	public function getExpire()
	{
		return $this->expire;
	}
	public function setExpire($seconds)
	{
		$this->expire = $seconds;
	}
}
