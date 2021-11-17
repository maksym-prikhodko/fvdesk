<?php namespace Illuminate\Session;
use SessionHandlerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
class Store implements SessionInterface {
	protected $id;
	protected $name;
	protected $attributes = array();
	protected $bags = array();
	protected $metaBag;
	protected $bagData = array();
	protected $handler;
	protected $started = false;
	public function __construct($name, SessionHandlerInterface $handler, $id = null)
	{
		$this->setId($id);
		$this->name = $name;
		$this->handler = $handler;
		$this->metaBag = new MetadataBag;
	}
	public function start()
	{
		$this->loadSession();
		if ( ! $this->has('_token')) $this->regenerateToken();
		return $this->started = true;
	}
	protected function loadSession()
	{
		$this->attributes = array_merge($this->attributes, $this->readFromHandler());
		foreach (array_merge($this->bags, array($this->metaBag)) as $bag)
		{
			$this->initializeLocalBag($bag);
			$bag->initialize($this->bagData[$bag->getStorageKey()]);
		}
	}
	protected function readFromHandler()
	{
		$data = $this->handler->read($this->getId());
		if ($data)
		{
			$data = @unserialize($this->prepareForUnserialize($data));
			if ($data !== false) return $data;
		}
		return [];
	}
	protected function prepareForUnserialize($data)
	{
		return $data;
	}
	protected function initializeLocalBag($bag)
	{
		$this->bagData[$bag->getStorageKey()] = $this->pull($bag->getStorageKey(), []);
	}
	public function getId()
	{
		return $this->id;
	}
	public function setId($id)
	{
		if ( ! $this->isValidId($id))
		{
			$id = $this->generateSessionId();
		}
		$this->id = $id;
	}
	public function isValidId($id)
	{
		return is_string($id) && preg_match('/^[a-f0-9]{40}$/', $id);
	}
	protected function generateSessionId()
	{
		return sha1(uniqid('', true).str_random(25).microtime(true));
	}
	public function getName()
	{
		return $this->name;
	}
	public function setName($name)
	{
		$this->name = $name;
	}
	public function invalidate($lifetime = null)
	{
		$this->clear();
		return $this->migrate(true, $lifetime);
	}
	public function migrate($destroy = false, $lifetime = null)
	{
		if ($destroy) $this->handler->destroy($this->getId());
		$this->setExists(false);
		$this->id = $this->generateSessionId();
		return true;
	}
	public function regenerate($destroy = false)
	{
		return $this->migrate($destroy);
	}
	public function save()
	{
		$this->addBagDataToSession();
		$this->ageFlashData();
		$this->handler->write($this->getId(), $this->prepareForStorage(serialize($this->attributes)));
		$this->started = false;
	}
	protected function prepareForStorage($data)
	{
		return $data;
	}
	protected function addBagDataToSession()
	{
		foreach (array_merge($this->bags, array($this->metaBag)) as $bag)
		{
			$this->put($bag->getStorageKey(), $this->bagData[$bag->getStorageKey()]);
		}
	}
	public function ageFlashData()
	{
		foreach ($this->get('flash.old', array()) as $old) { $this->forget($old); }
		$this->put('flash.old', $this->get('flash.new', array()));
		$this->put('flash.new', array());
	}
	public function has($name)
	{
		return ! is_null($this->get($name));
	}
	public function get($name, $default = null)
	{
		return array_get($this->attributes, $name, $default);
	}
	public function pull($key, $default = null)
	{
		return array_pull($this->attributes, $key, $default);
	}
	public function hasOldInput($key = null)
	{
		$old = $this->getOldInput($key);
		return is_null($key) ? count($old) > 0 : ! is_null($old);
	}
	public function getOldInput($key = null, $default = null)
	{
		$input = $this->get('_old_input', array());
		return array_get($input, $key, $default);
	}
	public function set($name, $value)
	{
		array_set($this->attributes, $name, $value);
	}
	public function put($key, $value = null)
	{
		if ( ! is_array($key)) $key = array($key => $value);
		foreach ($key as $arrayKey => $arrayValue)
		{
			$this->set($arrayKey, $arrayValue);
		}
	}
	public function push($key, $value)
	{
		$array = $this->get($key, array());
		$array[] = $value;
		$this->put($key, $array);
	}
	public function flash($key, $value)
	{
		$this->put($key, $value);
		$this->push('flash.new', $key);
		$this->removeFromOldFlashData(array($key));
	}
	public function flashInput(array $value)
	{
		$this->flash('_old_input', $value);
	}
	public function reflash()
	{
		$this->mergeNewFlashes($this->get('flash.old', array()));
		$this->put('flash.old', array());
	}
	public function keep($keys = null)
	{
		$keys = is_array($keys) ? $keys : func_get_args();
		$this->mergeNewFlashes($keys);
		$this->removeFromOldFlashData($keys);
	}
	protected function mergeNewFlashes(array $keys)
	{
		$values = array_unique(array_merge($this->get('flash.new', array()), $keys));
		$this->put('flash.new', $values);
	}
	protected function removeFromOldFlashData(array $keys)
	{
		$this->put('flash.old', array_diff($this->get('flash.old', array()), $keys));
	}
	public function all()
	{
		return $this->attributes;
	}
	public function replace(array $attributes)
	{
		$this->put($attributes);
	}
	public function remove($name)
	{
		return array_pull($this->attributes, $name);
	}
	public function forget($key)
	{
		array_forget($this->attributes, $key);
	}
	public function clear()
	{
		$this->attributes = array();
		foreach ($this->bags as $bag)
		{
			$bag->clear();
		}
	}
	public function flush()
	{
		$this->clear();
	}
	public function isStarted()
	{
		return $this->started;
	}
	public function registerBag(SessionBagInterface $bag)
	{
		$this->bags[$bag->getStorageKey()] = $bag;
	}
	public function getBag($name)
	{
		return array_get($this->bags, $name, function()
		{
			throw new InvalidArgumentException("Bag not registered.");
		});
	}
	public function getMetadataBag()
	{
		return $this->metaBag;
	}
	public function getBagData($name)
	{
		return array_get($this->bagData, $name, array());
	}
	public function token()
	{
		return $this->get('_token');
	}
	public function getToken()
	{
		return $this->token();
	}
	public function regenerateToken()
	{
		$this->put('_token', str_random(40));
	}
	public function previousUrl()
	{
		return $this->get('_previous.url');
	}
	public function setPreviousUrl($url)
	{
		return $this->put('_previous.url', $url);
	}
	public function setExists($value)
	{
		if ($this->handler instanceof ExistenceAwareInterface)
		{
			$this->handler->setExists($value);
		}
	}
	public function getHandler()
	{
		return $this->handler;
	}
	public function handlerNeedsRequest()
	{
		return $this->handler instanceof CookieSessionHandler;
	}
	public function setRequestOnHandler(Request $request)
	{
		if ($this->handlerNeedsRequest())
		{
			$this->handler->setRequest($request);
		}
	}
}
