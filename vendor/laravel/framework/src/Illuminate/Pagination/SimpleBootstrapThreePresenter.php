<?php namespace Illuminate\Pagination;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
class SimpleBootstrapThreePresenter extends BootstrapThreePresenter {
	public function __construct(PaginatorContract $paginator)
	{
		$this->paginator = $paginator;
	}
	public function hasPages()
	{
		return $this->paginator->hasPages() && count($this->paginator->items()) > 0;
	}
	public function render()
	{
		if ($this->hasPages())
		{
			return sprintf(
				'<ul class="pager">%s %s</ul>',
				$this->getPreviousButton(),
				$this->getNextButton()
			);
		}
		return '';
	}
}
