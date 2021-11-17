<?php namespace Illuminate\Pipeline;
use Illuminate\Support\ServiceProvider;
class PipelineServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->app->singleton(
			'Illuminate\Contracts\Pipeline\Hub', 'Illuminate\Pipeline\Hub'
		);
	}
	public function provides()
	{
		return [
			'Illuminate\Contracts\Pipeline\Hub',
		];
	}
}
