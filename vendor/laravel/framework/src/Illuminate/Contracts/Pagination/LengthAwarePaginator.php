<?php namespace Illuminate\Contracts\Pagination;
interface LengthAwarePaginator extends Paginator {
	public function total();
	public function lastPage();
}
