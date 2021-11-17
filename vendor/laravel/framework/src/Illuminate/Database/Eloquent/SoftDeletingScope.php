<?php namespace Illuminate\Database\Eloquent;
class SoftDeletingScope implements ScopeInterface {
	protected $extensions = ['ForceDelete', 'Restore', 'WithTrashed', 'OnlyTrashed'];
	public function apply(Builder $builder, Model $model)
	{
		$builder->whereNull($model->getQualifiedDeletedAtColumn());
		$this->extend($builder);
	}
	public function remove(Builder $builder, Model $model)
	{
		$column = $model->getQualifiedDeletedAtColumn();
		$query = $builder->getQuery();
		$query->wheres = collect($query->wheres)->reject(function($where) use ($column)
		{
			return $this->isSoftDeleteConstraint($where, $column);
		})->values()->all();
	}
	public function extend(Builder $builder)
	{
		foreach ($this->extensions as $extension)
		{
			$this->{"add{$extension}"}($builder);
		}
		$builder->onDelete(function(Builder $builder)
		{
			$column = $this->getDeletedAtColumn($builder);
			return $builder->update(array(
				$column => $builder->getModel()->freshTimestampString(),
			));
		});
	}
	protected function getDeletedAtColumn(Builder $builder)
	{
		if (count($builder->getQuery()->joins) > 0)
		{
			return $builder->getModel()->getQualifiedDeletedAtColumn();
		}
		else
		{
			return $builder->getModel()->getDeletedAtColumn();
		}
	}
	protected function addForceDelete(Builder $builder)
	{
		$builder->macro('forceDelete', function(Builder $builder)
		{
			return $builder->getQuery()->delete();
		});
	}
	protected function addRestore(Builder $builder)
	{
		$builder->macro('restore', function(Builder $builder)
		{
			$builder->withTrashed();
			return $builder->update(array($builder->getModel()->getDeletedAtColumn() => null));
		});
	}
	protected function addWithTrashed(Builder $builder)
	{
		$builder->macro('withTrashed', function(Builder $builder)
		{
			$this->remove($builder, $builder->getModel());
			return $builder;
		});
	}
	protected function addOnlyTrashed(Builder $builder)
	{
		$builder->macro('onlyTrashed', function(Builder $builder)
		{
			$model = $builder->getModel();
			$this->remove($builder, $model);
			$builder->getQuery()->whereNotNull($model->getQualifiedDeletedAtColumn());
			return $builder;
		});
	}
	protected function isSoftDeleteConstraint(array $where, $column)
	{
		return $where['type'] == 'Null' && $where['column'] == $column;
	}
}
