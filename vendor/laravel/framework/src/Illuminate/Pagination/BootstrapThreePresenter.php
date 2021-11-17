<?php namespace Illuminate\Pagination;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Pagination\Presenter as PresenterContract;
class BootstrapThreePresenter implements PresenterContract {
	use BootstrapThreeNextPreviousButtonRendererTrait, UrlWindowPresenterTrait;
	protected $paginator;
	protected $window;
	public function __construct(PaginatorContract $paginator, UrlWindow $window = null)
	{
		$this->paginator = $paginator;
		$this->window = is_null($window) ? UrlWindow::make($paginator) : $window->get();
	}
	public function hasPages()
	{
		return $this->paginator->hasPages();
	}
	public function render()
	{
		if ($this->hasPages())
		{
			return sprintf(
				'<ul class="pagination">%s %s %s</ul>',
				$this->getPreviousButton(),
				$this->getLinks(),
				$this->getNextButton()
			);
		}
		return '';
	}
	protected function getAvailablePageWrapper($url, $page, $rel = null)
	{
		$rel = is_null($rel) ? '' : ' rel="'.$rel.'"';
		return '<li><a href="'.htmlentities($url).'"'.$rel.'>'.$page.'</a></li>';
	}
	protected function getDisabledTextWrapper($text)
	{
		return '<li class="disabled"><span>'.$text.'</span></li>';
	}
	protected function getActivePageWrapper($text)
	{
		return '<li class="active"><span>'.$text.'</span></li>';
	}
	protected function getDots()
	{
		return $this->getDisabledTextWrapper("...");
	}
	protected function currentPage()
	{
		return $this->paginator->currentPage();
	}
	protected function lastPage()
	{
		return $this->paginator->lastPage();
	}
}
