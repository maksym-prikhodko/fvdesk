<?php namespace Illuminate\Database;
use PDO;
use Closure;
use DateTime;
use Exception;
use LogicException;
use RuntimeException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Query\Processors\Processor;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Illuminate\Database\Query\Grammars\Grammar as QueryGrammar;
class Connection implements ConnectionInterface {
	protected $pdo;
	protected $readPdo;
	protected $reconnector;
	protected $queryGrammar;
	protected $schemaGrammar;
	protected $postProcessor;
	protected $events;
	protected $fetchMode = PDO::FETCH_ASSOC;
	protected $transactions = 0;
	protected $queryLog = array();
	protected $loggingQueries = false;
	protected $pretending = false;
	protected $database;
	protected $tablePrefix = '';
	protected $config = array();
	public function __construct(PDO $pdo, $database = '', $tablePrefix = '', array $config = array())
	{
		$this->pdo = $pdo;
		$this->database = $database;
		$this->tablePrefix = $tablePrefix;
		$this->config = $config;
		$this->useDefaultQueryGrammar();
		$this->useDefaultPostProcessor();
	}
	public function useDefaultQueryGrammar()
	{
		$this->queryGrammar = $this->getDefaultQueryGrammar();
	}
	protected function getDefaultQueryGrammar()
	{
		return new QueryGrammar;
	}
	public function useDefaultSchemaGrammar()
	{
		$this->schemaGrammar = $this->getDefaultSchemaGrammar();
	}
	protected function getDefaultSchemaGrammar()
	{
	}
	public function useDefaultPostProcessor()
	{
		$this->postProcessor = $this->getDefaultPostProcessor();
	}
	protected function getDefaultPostProcessor()
	{
		return new Query\Processors\Processor;
	}
	public function getSchemaBuilder()
	{
		if (is_null($this->schemaGrammar)) { $this->useDefaultSchemaGrammar(); }
		return new Schema\Builder($this);
	}
	public function table($table)
	{
		$processor = $this->getPostProcessor();
		$query = new Query\Builder($this, $this->getQueryGrammar(), $processor);
		return $query->from($table);
	}
	public function raw($value)
	{
		return new Query\Expression($value);
	}
	public function selectOne($query, $bindings = array())
	{
		$records = $this->select($query, $bindings);
		return count($records) > 0 ? reset($records) : null;
	}
	public function selectFromWriteConnection($query, $bindings = array())
	{
		return $this->select($query, $bindings, false);
	}
	public function select($query, $bindings = array(), $useReadPdo = true)
	{
		return $this->run($query, $bindings, function($me, $query, $bindings) use ($useReadPdo)
		{
			if ($me->pretending()) return array();
			$statement = $this->getPdoForSelect($useReadPdo)->prepare($query);
			$statement->execute($me->prepareBindings($bindings));
			return $statement->fetchAll($me->getFetchMode());
		});
	}
	protected function getPdoForSelect($useReadPdo = true)
	{
		return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
	}
	public function insert($query, $bindings = array())
	{
		return $this->statement($query, $bindings);
	}
	public function update($query, $bindings = array())
	{
		return $this->affectingStatement($query, $bindings);
	}
	public function delete($query, $bindings = array())
	{
		return $this->affectingStatement($query, $bindings);
	}
	public function statement($query, $bindings = array())
	{
		return $this->run($query, $bindings, function($me, $query, $bindings)
		{
			if ($me->pretending()) return true;
			$bindings = $me->prepareBindings($bindings);
			return $me->getPdo()->prepare($query)->execute($bindings);
		});
	}
	public function affectingStatement($query, $bindings = array())
	{
		return $this->run($query, $bindings, function($me, $query, $bindings)
		{
			if ($me->pretending()) return 0;
			$statement = $me->getPdo()->prepare($query);
			$statement->execute($me->prepareBindings($bindings));
			return $statement->rowCount();
		});
	}
	public function unprepared($query)
	{
		return $this->run($query, array(), function($me, $query)
		{
			if ($me->pretending()) return true;
			return (bool) $me->getPdo()->exec($query);
		});
	}
	public function prepareBindings(array $bindings)
	{
		$grammar = $this->getQueryGrammar();
		foreach ($bindings as $key => $value)
		{
			if ($value instanceof DateTime)
			{
				$bindings[$key] = $value->format($grammar->getDateFormat());
			}
			elseif ($value === false)
			{
				$bindings[$key] = 0;
			}
		}
		return $bindings;
	}
	public function transaction(Closure $callback)
	{
		$this->beginTransaction();
		try
		{
			$result = $callback($this);
			$this->commit();
		}
		catch (Exception $e)
		{
			$this->rollBack();
			throw $e;
		}
		return $result;
	}
	public function beginTransaction()
	{
		++$this->transactions;
		if ($this->transactions == 1)
		{
			$this->pdo->beginTransaction();
		}
		$this->fireConnectionEvent('beganTransaction');
	}
	public function commit()
	{
		if ($this->transactions == 1) $this->pdo->commit();
		--$this->transactions;
		$this->fireConnectionEvent('committed');
	}
	public function rollBack()
	{
		if ($this->transactions == 1)
		{
			$this->transactions = 0;
			$this->pdo->rollBack();
		}
		else
		{
			--$this->transactions;
		}
		$this->fireConnectionEvent('rollingBack');
	}
	public function transactionLevel()
	{
		return $this->transactions;
	}
	public function pretend(Closure $callback)
	{
		$loggingQueries = $this->loggingQueries;
		$this->enableQueryLog();
		$this->pretending = true;
		$this->queryLog = [];
		$callback($this);
		$this->pretending = false;
		$this->loggingQueries = $loggingQueries;
		return $this->queryLog;
	}
	protected function run($query, $bindings, Closure $callback)
	{
		$this->reconnectIfMissingConnection();
		$start = microtime(true);
		try
		{
			$result = $this->runQueryCallback($query, $bindings, $callback);
		}
		catch (QueryException $e)
		{
			$result = $this->tryAgainIfCausedByLostConnection(
				$e, $query, $bindings, $callback
			);
		}
		$time = $this->getElapsedTime($start);
		$this->logQuery($query, $bindings, $time);
		return $result;
	}
	protected function runQueryCallback($query, $bindings, Closure $callback)
	{
		try
		{
			$result = $callback($this, $query, $bindings);
		}
		catch (Exception $e)
		{
			throw new QueryException(
				$query, $this->prepareBindings($bindings), $e
			);
		}
		return $result;
	}
	protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
	{
		if ($this->causedByLostConnection($e))
		{
			$this->reconnect();
			return $this->runQueryCallback($query, $bindings, $callback);
		}
		throw $e;
	}
	protected function causedByLostConnection(QueryException $e)
	{
		$message = $e->getPrevious()->getMessage();
		return str_contains($message, [
			'server has gone away',
			'no connection to the server',
			'Lost connection',
		]);
	}
	public function disconnect()
	{
		$this->setPdo(null)->setReadPdo(null);
	}
	public function reconnect()
	{
		if (is_callable($this->reconnector))
		{
			return call_user_func($this->reconnector, $this);
		}
		throw new LogicException("Lost connection and no reconnector available.");
	}
	protected function reconnectIfMissingConnection()
	{
		if (is_null($this->getPdo()) || is_null($this->getReadPdo()))
		{
			$this->reconnect();
		}
	}
	public function logQuery($query, $bindings, $time = null)
	{
		if (isset($this->events))
		{
			$this->events->fire('illuminate.query', array($query, $bindings, $time, $this->getName()));
		}
		if ( ! $this->loggingQueries) return;
		$this->queryLog[] = compact('query', 'bindings', 'time');
	}
	public function listen(Closure $callback)
	{
		if (isset($this->events))
		{
			$this->events->listen('illuminate.query', $callback);
		}
	}
	protected function fireConnectionEvent($event)
	{
		if (isset($this->events))
		{
			$this->events->fire('connection.'.$this->getName().'.'.$event, $this);
		}
	}
	protected function getElapsedTime($start)
	{
		return round((microtime(true) - $start) * 1000, 2);
	}
	public function getDoctrineColumn($table, $column)
	{
		$schema = $this->getDoctrineSchemaManager();
		return $schema->listTableDetails($table)->getColumn($column);
	}
	public function getDoctrineSchemaManager()
	{
		return $this->getDoctrineDriver()->getSchemaManager($this->getDoctrineConnection());
	}
	public function getDoctrineConnection()
	{
		$driver = $this->getDoctrineDriver();
		$data = array('pdo' => $this->pdo, 'dbname' => $this->getConfig('database'));
		return new DoctrineConnection($data, $driver);
	}
	public function getPdo()
	{
		return $this->pdo;
	}
	public function getReadPdo()
	{
		if ($this->transactions >= 1) return $this->getPdo();
		return $this->readPdo ?: $this->pdo;
	}
	public function setPdo($pdo)
	{
		if ($this->transactions >= 1)
			throw new RuntimeException("Can't swap PDO instance while within transaction.");
		$this->pdo = $pdo;
		return $this;
	}
	public function setReadPdo($pdo)
	{
		$this->readPdo = $pdo;
		return $this;
	}
	public function setReconnector(callable $reconnector)
	{
		$this->reconnector = $reconnector;
		return $this;
	}
	public function getName()
	{
		return $this->getConfig('name');
	}
	public function getConfig($option)
	{
		return array_get($this->config, $option);
	}
	public function getDriverName()
	{
		return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	}
	public function getQueryGrammar()
	{
		return $this->queryGrammar;
	}
	public function setQueryGrammar(Query\Grammars\Grammar $grammar)
	{
		$this->queryGrammar = $grammar;
	}
	public function getSchemaGrammar()
	{
		return $this->schemaGrammar;
	}
	public function setSchemaGrammar(Schema\Grammars\Grammar $grammar)
	{
		$this->schemaGrammar = $grammar;
	}
	public function getPostProcessor()
	{
		return $this->postProcessor;
	}
	public function setPostProcessor(Processor $processor)
	{
		$this->postProcessor = $processor;
	}
	public function getEventDispatcher()
	{
		return $this->events;
	}
	public function setEventDispatcher(Dispatcher $events)
	{
		$this->events = $events;
	}
	public function pretending()
	{
		return $this->pretending === true;
	}
	public function getFetchMode()
	{
		return $this->fetchMode;
	}
	public function setFetchMode($fetchMode)
	{
		$this->fetchMode = $fetchMode;
	}
	public function getQueryLog()
	{
		return $this->queryLog;
	}
	public function flushQueryLog()
	{
		$this->queryLog = array();
	}
	public function enableQueryLog()
	{
		$this->loggingQueries = true;
	}
	public function disableQueryLog()
	{
		$this->loggingQueries = false;
	}
	public function logging()
	{
		return $this->loggingQueries;
	}
	public function getDatabaseName()
	{
		return $this->database;
	}
	public function setDatabaseName($database)
	{
		$this->database = $database;
	}
	public function getTablePrefix()
	{
		return $this->tablePrefix;
	}
	public function setTablePrefix($prefix)
	{
		$this->tablePrefix = $prefix;
		$this->getQueryGrammar()->setTablePrefix($prefix);
	}
	public function withTablePrefix(Grammar $grammar)
	{
		$grammar->setTablePrefix($this->tablePrefix);
		return $grammar;
	}
}
