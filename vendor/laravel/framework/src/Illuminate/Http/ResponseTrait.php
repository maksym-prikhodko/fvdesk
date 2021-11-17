<?php namespace Illuminate\Http;
use Symfony\Component\HttpFoundation\Cookie;
trait ResponseTrait {
	public function header($key, $value, $replace = true)
	{
		$this->headers->set($key, $value, $replace);
		return $this;
	}
	public function withCookie(Cookie $cookie)
	{
		$this->headers->setCookie($cookie);
		return $this;
	}
}
