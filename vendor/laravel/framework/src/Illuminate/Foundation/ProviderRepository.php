<?php namespace Illuminate\Foundation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
class ProviderRepository {
	protected $app;
	protected $files;
	protected $manifestPath;
	public function __construct(ApplicationContract $app, Filesystem $files, $manifestPath)
	{
		$this->app = $app;
		$this->files = $files;
		$this->manifestPath = $manifestPath;
	}
	public function load(array $providers)
	{
		$manifest = $this->loadManifest();
		if ($this->shouldRecompile($manifest, $providers))
		{
			$manifest = $this->compileManifest($providers);
		}
		foreach ($manifest['when'] as $provider => $events)
		{
			$this->registerLoadEvents($provider, $events);
		}
		foreach ($manifest['eager'] as $provider)
		{
			$this->app->register($this->createProvider($provider));
		}
		$this->app->setDeferredServices($manifest['deferred']);
	}
	protected function registerLoadEvents($provider, array $events)
	{
		if (count($events) < 1) return;
		$app = $this->app;
		$app->make('events')->listen($events, function() use ($app, $provider)
		{
			$app->register($provider);
		});
	}
	protected function compileManifest($providers)
	{
		$manifest = $this->freshManifest($providers);
		foreach ($providers as $provider)
		{
			$instance = $this->createProvider($provider);
			if ($instance->isDeferred())
			{
				foreach ($instance->provides() as $service)
				{
					$manifest['deferred'][$service] = $provider;
				}
				$manifest['when'][$provider] = $instance->when();
			}
			else
			{
				$manifest['eager'][] = $provider;
			}
		}
		return $this->writeManifest($manifest);
	}
	public function createProvider($provider)
	{
		return new $provider($this->app);
	}
	public function shouldRecompile($manifest, $providers)
	{
		return is_null($manifest) || $manifest['providers'] != $providers;
	}
	public function loadManifest()
	{
		if ($this->files->exists($this->manifestPath))
		{
			$manifest = json_decode($this->files->get($this->manifestPath), true);
			return array_merge(['when' => []], $manifest);
		}
	}
	public function writeManifest($manifest)
	{
		$this->files->put(
			$this->manifestPath, json_encode($manifest, JSON_PRETTY_PRINT)
		);
		return $manifest;
	}
	protected function freshManifest(array $providers)
	{
		return ['providers' => $providers, 'eager' => [], 'deferred' => []];
	}
}
