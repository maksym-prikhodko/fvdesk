<?php namespace Illuminate\Routing;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Contracts\Routing\ResponseFactory as FactoryContract;
class ResponseFactory implements FactoryContract {
	use Macroable;
	protected $view;
	protected $redirector;
	public function __construct(ViewFactory $view, Redirector $redirector)
	{
		$this->view = $view;
		$this->redirector = $redirector;
	}
	public function make($content = '', $status = 200, array $headers = array())
	{
		return new Response($content, $status, $headers);
	}
	public function view($view, $data = array(), $status = 200, array $headers = array())
	{
		return static::make($this->view->make($view, $data), $status, $headers);
	}
	public function json($data = array(), $status = 200, array $headers = array(), $options = 0)
	{
		if ($data instanceof Arrayable)
		{
			$data = $data->toArray();
		}
		return new JsonResponse($data, $status, $headers, $options);
	}
	public function jsonp($callback, $data = array(), $status = 200, array $headers = array(), $options = 0)
	{
		return $this->json($data, $status, $headers, $options)->setCallback($callback);
	}
	public function stream($callback, $status = 200, array $headers = array())
	{
		return new StreamedResponse($callback, $status, $headers);
	}
	public function download($file, $name = null, array $headers = array(), $disposition = 'attachment')
	{
		$response = new BinaryFileResponse($file, 200, $headers, true, $disposition);
		if ( ! is_null($name))
		{
			return $response->setContentDisposition($disposition, $name, str_replace('%', '', Str::ascii($name)));
		}
		return $response;
	}
	public function redirectTo($path, $status = 302, $headers = array(), $secure = null)
	{
		return $this->redirector->to($path, $status, $headers, $secure);
	}
	public function redirectToRoute($route, $parameters = array(), $status = 302, $headers = array())
	{
		return $this->redirector->route($route, $parameters, $status, $headers);
	}
	public function redirectToAction($action, $parameters = array(), $status = 302, $headers = array())
	{
		return $this->redirector->action($action, $parameters, $status, $headers);
	}
	public function redirectGuest($path, $status = 302, $headers = array(), $secure = null)
	{
		return $this->redirector->guest($path, $status, $headers, $secure);
	}
	public function redirectToIntended($default = '/', $status = 302, $headers = array(), $secure = null)
	{
		return $this->redirector->intended($default, $status, $headers, $secure);
	}
}
