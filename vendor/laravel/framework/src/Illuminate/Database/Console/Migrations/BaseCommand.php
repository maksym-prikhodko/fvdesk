<?php namespace Illuminate\Database\Console\Migrations;
use Illuminate\Console\Command;
class BaseCommand extends Command {
	protected function getMigrationPath()
	{
		return $this->laravel->databasePath().'/migrations';
	}
}
