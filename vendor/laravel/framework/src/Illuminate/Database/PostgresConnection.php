<?php namespace Illuminate\Database;
use Doctrine\DBAL\Driver\PDOPgSql\Driver as DoctrineDriver;
use Illuminate\Database\Query\Processors\PostgresProcessor;
use Illuminate\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;
class PostgresConnection extends Connection {
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
		return new PostgresProcessor;
	}
	protected function getDoctrineDriver()
	{
		return new DoctrineDriver;
	}
}
