<?php namespace Illuminate\Pagination;
trait UrlWindowPresenterTrait {
	protected function getLinks()
	{
		$html = '';
		if (is_array($this->window['first']))
		{
			$html .= $this->getUrlLinks($this->window['first']);
		}
		if (is_array($this->window['slider']))
		{
			$html .= $this->getDots();
			$html .= $this->getUrlLinks($this->window['slider']);
		}
		if (is_array($this->window['last']))
		{
			$html .= $this->getDots();
			$html .= $this->getUrlLinks($this->window['last']);
		}
		return $html;
	}
	protected function getUrlLinks(array $urls)
	{
		$html = '';
		foreach ($urls as $page => $url)
		{
			$html .= $this->getPageLinkWrapper($url, $page);
		}
		return $html;
	}
	protected function getPageLinkWrapper($url, $page, $rel = null)
	{
		if ($page == $this->paginator->currentPage())
		{
			return $this->getActivePageWrapper($page);
		}
		return $this->getAvailablePageWrapper($url, $page, $rel);
	}
}
