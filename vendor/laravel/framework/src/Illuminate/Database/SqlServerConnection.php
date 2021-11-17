<?php namespace Illuminate\Database;
use Closure;
use Exception;
use Doctrine\DBAL\Driver\PDOSqlsrv\Driver as DoctrineDriver;
use Illuminate\Database\Query\Processors\SqlServerProcessor;
use Illuminate\Database\Query\Grammars\SqlServerGrammar as QueryGrammar;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar as SchemaGrammar;
class SqlServerConnection extends Connection {
	public function transaction(Closure $callback)
	{
		if ($this->getDriverName() == 'sqlsrv')
		{
			return parent::transaction($callback);
		}
		$this->pdo->exec('BEGIN TRAN');
		try
		{
			$result = $callback($this);
			$this->pdo->exec('COMMIT TRAN');
		}
		catch (Exception $e)
		{
			$this->pdo->exec('ROLLBACK TRAN');
			throw $e;
		}
		return $result;
	}
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar);
	}
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix(new SchemaGrammar);
	}
	protected function getDefaultPostProcessor()
	{
		return new SqlServerProcessor;
	}
	protected function getDoctrineDriver()
	{
		return new DoctrineDriver;
	}
}
