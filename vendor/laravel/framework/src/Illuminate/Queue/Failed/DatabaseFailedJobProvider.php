<?php namespace Illuminate\Queue\Failed;
use Carbon\Carbon;
use Illuminate\Database\ConnectionResolverInterface;
class DatabaseFailedJobProvider implements FailedJobProviderInterface {
	protected $resolver;
	protected $database;
	protected $table;
	public function __construct(ConnectionResolverInterface $resolver, $database, $table)
	{
		$this->table = $table;
		$this->resolver = $resolver;
		$this->database = $database;
	}
	public function log($connection, $queue, $payload)
	{
		$failed_at = Carbon::now();
		$this->getTable()->insert(compact('connection', 'queue', 'payload', 'failed_at'));
	}
	public function all()
	{
		return $this->getTable()->orderBy('id', 'desc')->get();
	}
	public function find($id)
	{
		return $this->getTable()->find($id);
	}
	public function forget($id)
	{
		return $this->getTable()->where('id', $id)->delete() > 0;
	}
	public function flush()
	{
		$this->getTable()->delete();
	}
	protected function getTable()
	{
		return $this->resolver->connection($this->database)->table($this->table);
	}
}
