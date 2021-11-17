<?php namespace Illuminate\Foundation\Providers;
use Illuminate\Support\AggregateServiceProvider;
class FoundationServiceProvider extends AggregateServiceProvider {
	protected $providers = [
		'Illuminate\Foundation\Providers\FormRequestServiceProvider',
	];
}
