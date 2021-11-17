<?php namespace Illuminate\Foundation\Http\Middleware;
use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\Security\Core\Util\StringUtils;
class VerifyCsrfToken implements Middleware {
	protected $encrypter;
	public function __construct(Encrypter $encrypter)
	{
		$this->encrypter = $encrypter;
	}
	public function handle($request, Closure $next)
	{
		if ($this->isReading($request) || $this->tokensMatch($request))
		{
			return $this->addCookieToResponse($request, $next($request));
		}
		throw new TokenMismatchException;
	}
	protected function tokensMatch($request)
	{
		$token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
		if ( ! $token && $header = $request->header('X-XSRF-TOKEN'))
		{
			$token = $this->encrypter->decrypt($header);
		}
		return StringUtils::equals($request->session()->token(), $token);
	}
	protected function addCookieToResponse($request, $response)
	{
		$response->headers->setCookie(
			new Cookie('XSRF-TOKEN', $request->session()->token(), time() + 60 * 120, '/', null, false, false)
		);
		return $response;
	}
	protected function isReading($request)
	{
		return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
	}
}
