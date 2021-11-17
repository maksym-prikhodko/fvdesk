<?php namespace Illuminate\Foundation\Console;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
class KeyGenerateCommand extends Command {
	protected $name = 'key:generate';
	protected $description = "Set the application key";
	public function fire()
	{
		$key = $this->getRandomKey();
		if ($this->option('show'))
		{
			return $this->line('<comment>'.$key.'</comment>');
		}
		$path = base_path('.env');
		if (file_exists($path))
		{
			file_put_contents($path, str_replace(
				$this->laravel['config']['app.key'], $key, file_get_contents($path)
			));
		}
		$this->laravel['config']['app.key'] = $key;
		$this->info("Application key [$key] set successfully.");
	}
	protected function getRandomKey()
	{
		return Str::random(32);
	}
	protected function getOptions()
	{
		return array(
			array('show', null, InputOption::VALUE_NONE, 'Simply display the key instead of modifying files.'),
		);
	}
}
