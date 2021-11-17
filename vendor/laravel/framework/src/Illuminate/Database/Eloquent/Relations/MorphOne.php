<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Collection;
class MorphOne extends MorphOneOrMany {
	public function getResults()
	{
		return $this->query->first();
	}
	public function initRelation(array $models, $relation)
	{
		foreach ($models as $model)
		{
			$model->setRelation($relation, null);
		}
		return $models;
	}
	public function match(array $models, Collection $results, $relation)
	{
		return $this->matchOne($models, $results, $relation);
	}
}