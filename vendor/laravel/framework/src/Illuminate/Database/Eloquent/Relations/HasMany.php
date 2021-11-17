<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Collection;
class HasMany extends HasOneOrMany {
	public function getResults()
	{
		return $this->query->get();
	}
	public function initRelation(array $models, $relation)
	{
		foreach ($models as $model)
		{
			$model->setRelation($relation, $this->related->newCollection());
		}
		return $models;
	}
	public function match(array $models, Collection $results, $relation)
	{
		return $this->matchMany($models, $results, $relation);
	}
}
