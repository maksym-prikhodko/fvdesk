<?php namespace Illuminate\Routing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Session\Store as SessionStore;
class Redirector {
	protected $generator;
	protected $session;
	public function __construct(UrlGenerator $generator)
	{
		$this->generator = $generator;
	}
	public function home($status = 302)
	{
		return $this->to($this->generator->route('home'), $status);
	}
	public function back($status = 302, $headers = array())
	{
		$back = $this->generator->previous();
		return $this->createRedirect($back, $status, $headers);
	}
	public function refresh($status = 302, $headers = array())
	{
		return $this->to($this->generator->getRequest()->path(), $status, $headers);
	}
	public function guest($path, $status = 302, $headers = array(), $secure = null)
	{
		$this->session->put('url.intended', $this->generator->full());
		return $this->to($path, $status, $headers, $secure);
	}
	public function intended($default = '/', $status = 302, $headers = array(), $secure = null)
	{
		$path = $this->session->pull('url.intended', $default);
		return $this->to($path, $status, $headers, $secure);
	}
	public function to($path, $status = 302, $headers = array(), $secure = null)
	{
		$path = $this->generator->to($path, array(), $secure);
		return $this->createRedirect($path, $status, $headers);
	}
	public function away($path, $status = 302, $headers = array())
	{
		return $this->createRedirect($path, $status, $headers);
	}
	public function secure($path, $status = 302, $headers = array())
	{
		return $this->to($path, $status, $headers, true);
	}
	public function route($route, $parameters = array(), $status = 302, $headers = array())
	{
		$path = $this->generator->route($route, $parameters);
		return $this->to($path, $status, $headers);
	}
	public function action($action, $parameters = array(), $status = 302, $headers = array())
	{
		$path = $this->generator->action($action, $parameters);
		return $this->to($path, $status, $headers);
	}
	protected function createRedirect($path, $status, $headers)
	{
		$redirect = new RedirectResponse($path, $status, $headers);
		if (isset($this->session))
		{
			$redirect->setSession($this->session);
		}
		$redirect->setRequest($this->generator->getRequest());
		return $redirect;
	}
	public function getUrlGenerator()
	{
		return $this->generator;
	}
	public function setSession(SessionStore $session)
	{
		$this->session = $session;
	}
}
