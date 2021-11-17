<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
class ServeCommand extends Command {
	protected $name = 'serve';
	protected $description = "Serve the application on the PHP development server";
	public function fire()
	{
		chdir($this->laravel->publicPath());
		$host = $this->input->getOption('host');
		$port = $this->input->getOption('port');
		$base = $this->laravel->basePath();
		$this->info("Laravel development server started on http:
		passthru('"'.PHP_BINARY.'"'." -S {$host}:{$port} \"{$base}\"/server.php");
	}
	protected function getOptions()
	{
		return array(
			array('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', 'localhost'),
			array('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 8000),
		);
	}
}
