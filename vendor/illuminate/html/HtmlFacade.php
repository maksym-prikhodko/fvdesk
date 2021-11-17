<?php namespace Illuminate\Html;
use Illuminate\Support\Facades\Facade;
class HtmlFacade extends Facade {
	protected static function getFacadeAccessor() { return 'html'; }
}
