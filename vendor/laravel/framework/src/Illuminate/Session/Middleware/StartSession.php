<?php namespace Illuminate\Session\Middleware;
use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Session\CookieSessionHandler;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Routing\TerminableMiddleware;
class StartSession implements TerminableMiddleware {
	protected $manager;
	protected $sessionHandled = false;
	public function __construct(SessionManager $manager)
	{
		$this->manager = $manager;
	}
	public function handle($request, Closure $next)
	{
		$this->sessionHandled = true;
		if ($this->sessionConfigured())
		{
			$session = $this->startSession($request);
			$request->setSession($session);
		}
		$response = $next($request);
		if ($this->sessionConfigured())
		{
			$this->storeCurrentUrl($request, $session);
			$this->collectGarbage($session);
			$this->addCookieToResponse($response, $session);
		}
		return $response;
	}
	public function terminate($request, $response)
	{
		if ($this->sessionHandled && $this->sessionConfigured() && ! $this->usingCookieSessions())
		{
			$this->manager->driver()->save();
		}
	}
	protected function startSession(Request $request)
	{
		with($session = $this->getSession($request))->setRequestOnHandler($request);
		$session->start();
		return $session;
	}
	public function getSession(Request $request)
	{
		$session = $this->manager->driver();
		$session->setId($request->cookies->get($session->getName()));
		return $session;
	}
	protected function storeCurrentUrl(Request $request, $session)
	{
		if ($request->method() === 'GET' && $request->route() && ! $request->ajax())
		{
			$session->setPreviousUrl($request->fullUrl());
		}
	}
	protected function collectGarbage(SessionInterface $session)
	{
		$config = $this->manager->getSessionConfig();
		if ($this->configHitsLottery($config))
		{
			$session->getHandler()->gc($this->getSessionLifetimeInSeconds());
		}
	}
	protected function configHitsLottery(array $config)
	{
		return mt_rand(1, $config['lottery'][1]) <= $config['lottery'][0];
	}
	protected function addCookieToResponse(Response $response, SessionInterface $session)
	{
		if ($this->usingCookieSessions())
		{
			$this->manager->driver()->save();
		}
		if ($this->sessionIsPersistent($config = $this->manager->getSessionConfig()))
		{
			$response->headers->setCookie(new Cookie(
				$session->getName(), $session->getId(), $this->getCookieExpirationDate(),
				$config['path'], $config['domain'], array_get($config, 'secure', false)
			));
		}
	}
	protected function getSessionLifetimeInSeconds()
	{
		return array_get($this->manager->getSessionConfig(), 'lifetime') * 60;
	}
	protected function getCookieExpirationDate()
	{
		$config = $this->manager->getSessionConfig();
		return $config['expire_on_close'] ? 0 : Carbon::now()->addMinutes($config['lifetime']);
	}
	protected function sessionConfigured()
	{
		return ! is_null(array_get($this->manager->getSessionConfig(), 'driver'));
	}
	protected function sessionIsPersistent(array $config = null)
	{
		$config = $config ?: $this->manager->getSessionConfig();
		return ! in_array($config['driver'], array(null, 'array'));
	}
	protected function usingCookieSessions()
	{
		if ( ! $this->sessionConfigured()) return false;
		return $this->manager->driver()->getHandler() instanceof CookieSessionHandler;
	}
}
