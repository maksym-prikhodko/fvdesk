<?php namespace Illuminate\Pagination;
use Countable;
use ArrayAccess;
use IteratorAggregate;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Pagination\Presenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
class LengthAwarePaginator extends AbstractPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, Jsonable, LengthAwarePaginatorContract {
	protected $total;
	protected $lastPage;
	public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
	{
		foreach ($options as $key => $value)
		{
			$this->{$key} = $value;
		}
		$this->total = $total;
		$this->perPage = $perPage;
		$this->lastPage = (int) ceil($total / $perPage);
		$this->currentPage = $this->setCurrentPage($currentPage, $this->lastPage);
		$this->path = $this->path != '/' ? rtrim($this->path, '/').'/' : $this->path;
		$this->items = $items instanceof Collection ? $items : Collection::make($items);
	}
	protected function setCurrentPage($currentPage, $lastPage)
	{
		$currentPage = $currentPage ?: static::resolveCurrentPage();
		if (is_numeric($currentPage) && $currentPage > $lastPage)
		{
			return $lastPage > 0 ? $lastPage : 1;
		}
		return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
	}
	public function nextPageUrl()
	{
		if ($this->lastPage() > $this->currentPage())
		{
			return $this->url($this->currentPage() + 1);
		}
	}
	public function hasMorePages()
	{
		return $this->currentPage() < $this->lastPage();
	}
	public function total()
	{
		return $this->total;
	}
	public function lastPage()
	{
		return $this->lastPage;
	}
	public function render(Presenter $presenter = null)
	{
		if (is_null($presenter) && static::$presenterResolver)
		{
			$presenter = call_user_func(static::$presenterResolver, $this);
		}
		$presenter = $presenter ?: new BootstrapThreePresenter($this);
		return $presenter->render();
	}
	public function toArray()
	{
		return [
			'total'         => $this->total(),
			'per_page'      => $this->perPage(),
			'current_page'  => $this->currentPage(),
			'last_page'     => $this->lastPage(),
			'next_page_url' => $this->nextPageUrl(),
			'prev_page_url' => $this->previousPageUrl(),
			'from'          => $this->firstItem(),
			'to'            => $this->lastItem(),
			'data'          => $this->items->toArray(),
		];
	}
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}
}
