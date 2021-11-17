<?php namespace Illuminate\Database\Eloquent;
use RuntimeException;
class ModelNotFoundException extends RuntimeException {
	protected $model;
	public function setModel($model)
	{
		$this->model = $model;
		$this->message = "No query results for model [{$model}].";
		return $this;
	}
	public function getModel()
	{
		return $this->model;
	}
}
