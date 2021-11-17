<?php namespace Illuminate\Cookie\Middleware;
use Closure;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Contracts\Routing\Middleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
class EncryptCookies implements Middleware {
	protected $encrypter;
	public function __construct(EncrypterContract $encrypter)
	{
		$this->encrypter = $encrypter;
	}
	public function handle($request, Closure $next)
	{
		return $this->encrypt($next($this->decrypt($request)));
	}
	protected function decrypt(Request $request)
	{
		foreach ($request->cookies as $key => $c)
		{
			try
			{
				$request->cookies->set($key, $this->decryptCookie($c));
			}
			catch (DecryptException $e)
			{
				$request->cookies->set($key, null);
			}
		}
		return $request;
	}
	protected function decryptCookie($cookie)
	{
		return is_array($cookie)
						? $this->decryptArray($cookie)
						: $this->encrypter->decrypt($cookie);
	}
	protected function decryptArray(array $cookie)
	{
		$decrypted = array();
		foreach ($cookie as $key => $value)
		{
			$decrypted[$key] = $this->encrypter->decrypt($value);
		}
		return $decrypted;
	}
	protected function encrypt(Response $response)
	{
		foreach ($response->headers->getCookies() as $key => $cookie)
		{
			$response->headers->setCookie($this->duplicate(
				$cookie, $this->encrypter->encrypt($cookie->getValue())
			));
		}
		return $response;
	}
	protected function duplicate(Cookie $c, $value)
	{
		return new Cookie(
			$c->getName(), $value, $c->getExpiresTime(), $c->getPath(),
			$c->getDomain(), $c->isSecure(), $c->isHttpOnly()
		);
	}
}
