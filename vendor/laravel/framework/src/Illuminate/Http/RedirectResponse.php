<?php namespace Illuminate\Http;
use BadMethodCallException;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Contracts\Support\MessageProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse as BaseRedirectResponse;
class RedirectResponse extends BaseRedirectResponse {
	use ResponseTrait;
	protected $request;
	protected $session;
	public function with($key, $value = null)
	{
		$key = is_array($key) ? $key : [$key => $value];
		foreach ($key as $k => $v)
		{
			$this->session->flash($k, $v);
		}
		return $this;
	}
	public function withCookies(array $cookies)
	{
		foreach ($cookies as $cookie)
		{
			$this->headers->setCookie($cookie);
		}
		return $this;
	}
	public function withInput(array $input = null)
	{
		$input = $input ?: $this->request->input();
		$this->session->flashInput($data = array_filter($input, $callback = function (&$value) use (&$callback)
		{
			if (is_array($value))
			{
				$value = array_filter($value, $callback);
			}
			return ! $value instanceof UploadedFile;
		}));
		return $this;
	}
	public function onlyInput()
	{
		return $this->withInput($this->request->only(func_get_args()));
	}
	public function exceptInput()
	{
		return $this->withInput($this->request->except(func_get_args()));
	}
	public function withErrors($provider, $key = 'default')
	{
		$value = $this->parseErrors($provider);
		$this->session->flash(
			'errors', $this->session->get('errors', new ViewErrorBag)->put($key, $value)
		);
		return $this;
	}
	protected function parseErrors($provider)
	{
		if ($provider instanceof MessageProvider)
		{
			return $provider->getMessageBag();
		}
		return new MessageBag((array) $provider);
	}
	public function getRequest()
	{
		return $this->request;
	}
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}
	public function getSession()
	{
		return $this->session;
	}
	public function setSession(SessionStore $session)
	{
		$this->session = $session;
	}
	public function __call($method, $parameters)
	{
		if (starts_with($method, 'with'))
		{
			return $this->with(snake_case(substr($method, 4)), $parameters[0]);
		}
		throw new BadMethodCallException("Method [$method] does not exist on Redirect.");
	}
}
