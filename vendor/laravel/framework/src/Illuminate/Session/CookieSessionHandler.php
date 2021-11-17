<?php namespace Illuminate\Session;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
class CookieSessionHandler implements SessionHandlerInterface {
	protected $cookie;
	protected $request;
	public function __construct(CookieJar $cookie, $minutes)
	{
		$this->cookie = $cookie;
		$this->minutes = $minutes;
	}
	public function open($savePath, $sessionName)
	{
		return true;
	}
	public function close()
	{
		return true;
	}
	public function read($sessionId)
	{
		return $this->request->cookies->get($sessionId) ?: '';
	}
	public function write($sessionId, $data)
	{
		$this->cookie->queue($sessionId, $data, $this->minutes);
	}
	public function destroy($sessionId)
	{
		$this->cookie->queue($this->cookie->forget($sessionId));
	}
	public function gc($lifetime)
	{
		return true;
	}
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}
}
