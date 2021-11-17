<?php namespace Illuminate\Pagination;
trait BootstrapThreeNextPreviousButtonRendererTrait {
	protected function getPreviousButton($text = '&laquo;')
	{
		if ($this->paginator->currentPage() <= 1)
		{
			return $this->getDisabledTextWrapper($text);
		}
		$url = $this->paginator->url(
			$this->paginator->currentPage() - 1
		);
		return $this->getPageLinkWrapper($url, $text, 'prev');
	}
	protected function getNextButton($text = '&raquo;')
	{
		if ( ! $this->paginator->hasMorePages())
		{
			return $this->getDisabledTextWrapper($text);
		}
		$url = $this->paginator->url($this->paginator->currentPage() + 1);
		return $this->getPageLinkWrapper($url, $text, 'next');
	}
}
