<?php namespace Illuminate\Html;
use Illuminate\Support\Facades\Facade;
class FormFacade extends Facade {
	protected static function getFacadeAccessor() { return 'form'; }
}
