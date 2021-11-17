<?php namespace Illuminate\Foundation\Console\Tinker\Presenters;
use Psy\Presenter\ArrayPresenter;
use Illuminate\Support\Collection;
class IlluminateCollectionPresenter extends ArrayPresenter {
	public function canPresent($value)
	{
		return $value instanceof Collection;
	}
	protected function isArrayObject($value)
	{
		return $value instanceof Collection;
	}
	protected function getArrayObjectValue($value)
	{
		return $value->all();
	}
}
