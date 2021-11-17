<?php namespace Illuminate\View;
use Closure;
use ArrayAccess;
use BadMethodCallException;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\View\Engines\EngineInterface;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Contracts\View\View as ViewContract;
class View implements ArrayAccess, ViewContract {
	protected $factory;
	protected $engine;
	protected $view;
	protected $data;
	protected $path;
	public function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = array())
	{
		$this->view = $view;
		$this->path = $path;
		$this->engine = $engine;
		$this->factory = $factory;
		$this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;
	}
	public function render(Closure $callback = null)
	{
		$contents = $this->renderContents();
		$response = isset($callback) ? $callback($this, $contents) : null;
		$this->factory->flushSectionsIfDoneRendering();
		return $response ?: $contents;
	}
	protected function renderContents()
	{
		$this->factory->incrementRender();
		$this->factory->callComposer($this);
		$contents = $this->getContents();
		$this->factory->decrementRender();
		return $contents;
	}
	public function renderSections()
	{
		$env = $this->factory;
		return $this->render(function($view) use ($env)
		{
			return $env->getSections();
		});
	}
	protected function getContents()
	{
		return $this->engine->get($this->path, $this->gatherData());
	}
	protected function gatherData()
	{
		$data = array_merge($this->factory->getShared(), $this->data);
		foreach ($data as $key => $value)
		{
			if ($value instanceof Renderable)
			{
				$data[$key] = $value->render();
			}
		}
		return $data;
	}
	public function with($key, $value = null)
	{
		if (is_array($key))
		{
			$this->data = array_merge($this->data, $key);
		}
		else
		{
			$this->data[$key] = $value;
		}
		return $this;
	}
	public function nest($key, $view, array $data = array())
	{
		return $this->with($key, $this->factory->make($view, $data));
	}
	public function withErrors($provider)
	{
		if ($provider instanceof MessageProvider)
		{
			$this->with('errors', $provider->getMessageBag());
		}
		else
		{
			$this->with('errors', new MessageBag((array) $provider));
		}
		return $this;
	}
	public function getFactory()
	{
		return $this->factory;
	}
	public function getEngine()
	{
		return $this->engine;
	}
	public function name()
	{
		return $this->getName();
	}
	public function getName()
	{
		return $this->view;
	}
	public function getData()
	{
		return $this->data;
	}
	public function getPath()
	{
		return $this->path;
	}
	public function setPath($path)
	{
		$this->path = $path;
	}
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->data);
	}
	public function offsetGet($key)
	{
		return $this->data[$key];
	}
	public function offsetSet($key, $value)
	{
		$this->with($key, $value);
	}
	public function offsetUnset($key)
	{
		unset($this->data[$key]);
	}
	public function &__get($key)
	{
		return $this->data[$key];
	}
	public function __set($key, $value)
	{
		$this->with($key, $value);
	}
	public function __isset($key)
	{
		return isset($this->data[$key]);
	}
	public function __unset($key)
	{
		unset($this->data[$key]);
	}
	public function __call($method, $parameters)
	{
		if (starts_with($method, 'with'))
		{
			return $this->with(snake_case(substr($method, 4)), $parameters[0]);
		}
		throw new BadMethodCallException("Method [$method] does not exist on view.");
	}
	public function __toString()
	{
		return $this->render();
	}
}
