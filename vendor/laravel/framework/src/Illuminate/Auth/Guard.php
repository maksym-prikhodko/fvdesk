<?php namespace Illuminate\Auth;
use RuntimeException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
class Guard implements GuardContract {
	protected $user;
	protected $lastAttempted;
	protected $viaRemember = false;
	protected $provider;
	protected $session;
	protected $cookie;
	protected $request;
	protected $events;
	protected $loggedOut = false;
	protected $tokenRetrievalAttempted = false;
	public function __construct(UserProvider $provider,
								SessionInterface $session,
								Request $request = null)
	{
		$this->session = $session;
		$this->request = $request;
		$this->provider = $provider;
	}
	public function check()
	{
		return ! is_null($this->user());
	}
	public function guest()
	{
		return ! $this->check();
	}
	public function user()
	{
		if ($this->loggedOut) return;
		if ( ! is_null($this->user))
		{
			return $this->user;
		}
		$id = $this->session->get($this->getName());
		$user = null;
		if ( ! is_null($id))
		{
			$user = $this->provider->retrieveById($id);
		}
		$recaller = $this->getRecaller();
		if (is_null($user) && ! is_null($recaller))
		{
			$user = $this->getUserByRecaller($recaller);
			if ($user)
			{
				$this->updateSession($user->getAuthIdentifier());
				$this->fireLoginEvent($user, true);
			}
		}
		return $this->user = $user;
	}
	public function id()
	{
		if ($this->loggedOut) return;
		$id = $this->session->get($this->getName(), $this->getRecallerId());
		if (is_null($id) && $this->user())
		{
			$id = $this->user()->getAuthIdentifier();
		}
		return $id;
	}
	protected function getUserByRecaller($recaller)
	{
		if ($this->validRecaller($recaller) && ! $this->tokenRetrievalAttempted)
		{
			$this->tokenRetrievalAttempted = true;
			list($id, $token) = explode('|', $recaller, 2);
			$this->viaRemember = ! is_null($user = $this->provider->retrieveByToken($id, $token));
			return $user;
		}
	}
	protected function getRecaller()
	{
		return $this->request->cookies->get($this->getRecallerName());
	}
	protected function getRecallerId()
	{
		if ($this->validRecaller($recaller = $this->getRecaller()))
		{
			return head(explode('|', $recaller));
		}
	}
	protected function validRecaller($recaller)
	{
		if ( ! is_string($recaller) || ! str_contains($recaller, '|')) return false;
		$segments = explode('|', $recaller);
		return count($segments) == 2 && trim($segments[0]) !== '' && trim($segments[1]) !== '';
	}
	public function once(array $credentials = [])
	{
		if ($this->validate($credentials))
		{
			$this->setUser($this->lastAttempted);
			return true;
		}
		return false;
	}
	public function validate(array $credentials = [])
	{
		return $this->attempt($credentials, false, false);
	}
	public function basic($field = 'email')
	{
		if ($this->check()) return;
		if ($this->attemptBasic($this->getRequest(), $field)) return;
		return $this->getBasicResponse();
	}
	public function onceBasic($field = 'email')
	{
		if ( ! $this->once($this->getBasicCredentials($this->getRequest(), $field)))
		{
			return $this->getBasicResponse();
		}
	}
	protected function attemptBasic(Request $request, $field)
	{
		if ( ! $request->getUser()) return false;
		return $this->attempt($this->getBasicCredentials($request, $field));
	}
	protected function getBasicCredentials(Request $request, $field)
	{
		return [$field => $request->getUser(), 'password' => $request->getPassword()];
	}
	protected function getBasicResponse()
	{
		$headers = ['WWW-Authenticate' => 'Basic'];
		return new Response('Invalid credentials.', 401, $headers);
	}
	public function attempt(array $credentials = [], $remember = false, $login = true)
	{
		$this->fireAttemptEvent($credentials, $remember, $login);
		$this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
		if ($this->hasValidCredentials($user, $credentials))
		{
			if ($login) $this->login($user, $remember);
			return true;
		}
		return false;
	}
	protected function hasValidCredentials($user, $credentials)
	{
		return ! is_null($user) && $this->provider->validateCredentials($user, $credentials);
	}
	protected function fireAttemptEvent(array $credentials, $remember, $login)
	{
		if ($this->events)
		{
			$payload = [$credentials, $remember, $login];
			$this->events->fire('auth.attempt', $payload);
		}
	}
	public function attempting($callback)
	{
		if ($this->events)
		{
			$this->events->listen('auth.attempt', $callback);
		}
	}
	public function login(UserContract $user, $remember = false)
	{
		$this->updateSession($user->getAuthIdentifier());
		if ($remember)
		{
			$this->createRememberTokenIfDoesntExist($user);
			$this->queueRecallerCookie($user);
		}
		$this->fireLoginEvent($user, $remember);
		$this->setUser($user);
	}
	protected function fireLoginEvent($user, $remember = false)
	{
		if (isset($this->events))
		{
			$this->events->fire('auth.login', [$user, $remember]);
		}
	}
	protected function updateSession($id)
	{
		$this->session->set($this->getName(), $id);
		$this->session->migrate(true);
	}
	public function loginUsingId($id, $remember = false)
	{
		$this->session->set($this->getName(), $id);
		$this->login($user = $this->provider->retrieveById($id), $remember);
		return $user;
	}
	public function onceUsingId($id)
	{
		$this->setUser($this->provider->retrieveById($id));
		return $this->user instanceof UserContract;
	}
	protected function queueRecallerCookie(UserContract $user)
	{
		$value = $user->getAuthIdentifier().'|'.$user->getRememberToken();
		$this->getCookieJar()->queue($this->createRecaller($value));
	}
	protected function createRecaller($value)
	{
		return $this->getCookieJar()->forever($this->getRecallerName(), $value);
	}
	public function logout()
	{
		$user = $this->user();
		$this->clearUserDataFromStorage();
		if ( ! is_null($this->user))
		{
			$this->refreshRememberToken($user);
		}
		if (isset($this->events))
		{
			$this->events->fire('auth.logout', [$user]);
		}
		$this->user = null;
		$this->loggedOut = true;
	}
	protected function clearUserDataFromStorage()
	{
		$this->session->remove($this->getName());
		$recaller = $this->getRecallerName();
		$this->getCookieJar()->queue($this->getCookieJar()->forget($recaller));
	}
	protected function refreshRememberToken(UserContract $user)
	{
		$user->setRememberToken($token = str_random(60));
		$this->provider->updateRememberToken($user, $token);
	}
	protected function createRememberTokenIfDoesntExist(UserContract $user)
	{
		$rememberToken = $user->getRememberToken();
		if (empty($rememberToken))
		{
			$this->refreshRememberToken($user);
		}
	}
	public function getCookieJar()
	{
		if ( ! isset($this->cookie))
		{
			throw new RuntimeException("Cookie jar has not been set.");
		}
		return $this->cookie;
	}
	public function setCookieJar(CookieJar $cookie)
	{
		$this->cookie = $cookie;
	}
	public function getDispatcher()
	{
		return $this->events;
	}
	public function setDispatcher(Dispatcher $events)
	{
		$this->events = $events;
	}
	public function getSession()
	{
		return $this->session;
	}
	public function getProvider()
	{
		return $this->provider;
	}
	public function setProvider(UserProvider $provider)
	{
		$this->provider = $provider;
	}
	public function getUser()
	{
		return $this->user;
	}
	public function setUser(UserContract $user)
	{
		$this->user = $user;
		$this->loggedOut = false;
	}
	public function getRequest()
	{
		return $this->request ?: Request::createFromGlobals();
	}
	public function setRequest(Request $request)
	{
		$this->request = $request;
		return $this;
	}
	public function getLastAttempted()
	{
		return $this->lastAttempted;
	}
	public function getName()
	{
		return 'login_'.md5(get_class($this));
	}
	public function getRecallerName()
	{
		return 'remember_'.md5(get_class($this));
	}
	public function viaRemember()
	{
		return $this->viaRemember;
	}
}
