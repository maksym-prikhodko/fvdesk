<?php namespace Illuminate\View;
use Closure;
use InvalidArgumentException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\View\Factory as FactoryContract;
class Factory implements FactoryContract {
	protected $engines;
	protected $finder;
	protected $events;
	protected $container;
	protected $shared = array();
	protected $aliases = array();
	protected $names = array();
	protected $extensions = array('blade.php' => 'blade', 'php' => 'php');
	protected $composers = array();
	protected $sections = array();
	protected $sectionStack = array();
	protected $renderCount = 0;
	public function __construct(EngineResolver $engines, ViewFinderInterface $finder, Dispatcher $events)
	{
		$this->finder = $finder;
		$this->events = $events;
		$this->engines = $engines;
		$this->share('__env', $this);
	}
	public function file($path, $data = array(), $mergeData = array())
	{
		$data = array_merge($mergeData, $this->parseData($data));
		$this->callCreator($view = new View($this, $this->getEngineFromPath($path), $path, $path, $data));
		return $view;
	}
	public function make($view, $data = array(), $mergeData = array())
	{
		if (isset($this->aliases[$view])) $view = $this->aliases[$view];
		$view = $this->normalizeName($view);
		$path = $this->finder->find($view);
		$data = array_merge($mergeData, $this->parseData($data));
		$this->callCreator($view = new View($this, $this->getEngineFromPath($path), $view, $path, $data));
		return $view;
	}
	protected function normalizeName($name)
	{
		$delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;
		if (strpos($name, $delimiter) === false)
		{
			return str_replace('/', '.', $name);
		}
		list($namespace, $name) = explode($delimiter, $name);
		return $namespace . $delimiter . str_replace('/', '.', $name);
	}
	protected function parseData($data)
	{
		return $data instanceof Arrayable ? $data->toArray() : $data;
	}
	public function of($view, $data = array())
	{
		return $this->make($this->names[$view], $data);
	}
	public function name($view, $name)
	{
		$this->names[$name] = $view;
	}
	public function alias($view, $alias)
	{
		$this->aliases[$alias] = $view;
	}
	public function exists($view)
	{
		try
		{
			$this->finder->find($view);
		}
		catch (InvalidArgumentException $e)
		{
			return false;
		}
		return true;
	}
	public function renderEach($view, $data, $iterator, $empty = 'raw|')
	{
		$result = '';
		if (count($data) > 0)
		{
			foreach ($data as $key => $value)
			{
				$data = array('key' => $key, $iterator => $value);
				$result .= $this->make($view, $data)->render();
			}
		}
		else
		{
			if (starts_with($empty, 'raw|'))
			{
				$result = substr($empty, 4);
			}
			else
			{
				$result = $this->make($empty)->render();
			}
		}
		return $result;
	}
	public function getEngineFromPath($path)
	{
		if ( ! $extension = $this->getExtension($path))
		{
			throw new InvalidArgumentException("Unrecognized extension in file: $path");
		}
		$engine = $this->extensions[$extension];
		return $this->engines->resolve($engine);
	}
	protected function getExtension($path)
	{
		$extensions = array_keys($this->extensions);
		return array_first($extensions, function($key, $value) use ($path)
		{
			return ends_with($path, $value);
		});
	}
	public function share($key, $value = null)
	{
		if ( ! is_array($key)) return $this->shared[$key] = $value;
		foreach ($key as $innerKey => $innerValue)
		{
			$this->share($innerKey, $innerValue);
		}
	}
	public function creator($views, $callback)
	{
		$creators = array();
		foreach ((array) $views as $view)
		{
			$creators[] = $this->addViewEvent($view, $callback, 'creating: ');
		}
		return $creators;
	}
	public function composers(array $composers)
	{
		$registered = array();
		foreach ($composers as $callback => $views)
		{
			$registered = array_merge($registered, $this->composer($views, $callback));
		}
		return $registered;
	}
	public function composer($views, $callback, $priority = null)
	{
		$composers = array();
		foreach ((array) $views as $view)
		{
			$composers[] = $this->addViewEvent($view, $callback, 'composing: ', $priority);
		}
		return $composers;
	}
	protected function addViewEvent($view, $callback, $prefix = 'composing: ', $priority = null)
	{
		$view = $this->normalizeName($view);
		if ($callback instanceof Closure)
		{
			$this->addEventListener($prefix.$view, $callback, $priority);
			return $callback;
		}
		elseif (is_string($callback))
		{
			return $this->addClassEvent($view, $callback, $prefix, $priority);
		}
	}
	protected function addClassEvent($view, $class, $prefix, $priority = null)
	{
		$name = $prefix.$view;
		$callback = $this->buildClassEventCallback($class, $prefix);
		$this->addEventListener($name, $callback, $priority);
		return $callback;
	}
	protected function addEventListener($name, $callback, $priority = null)
	{
		if (is_null($priority))
		{
			$this->events->listen($name, $callback);
		}
		else
		{
			$this->events->listen($name, $callback, $priority);
		}
	}
	protected function buildClassEventCallback($class, $prefix)
	{
		list($class, $method) = $this->parseClassEvent($class, $prefix);
		return function() use ($class, $method)
		{
			$callable = array($this->container->make($class), $method);
			return call_user_func_array($callable, func_get_args());
		};
	}
	protected function parseClassEvent($class, $prefix)
	{
		if (str_contains($class, '@'))
		{
			return explode('@', $class);
		}
		$method = str_contains($prefix, 'composing') ? 'compose' : 'create';
		return array($class, $method);
	}
	public function callComposer(View $view)
	{
		$this->events->fire('composing: '.$view->getName(), array($view));
	}
	public function callCreator(View $view)
	{
		$this->events->fire('creating: '.$view->getName(), array($view));
	}
	public function startSection($section, $content = '')
	{
		if ($content === '')
		{
			if (ob_start())
			{
				$this->sectionStack[] = $section;
			}
		}
		else
		{
			$this->extendSection($section, $content);
		}
	}
	public function inject($section, $content)
	{
		return $this->startSection($section, $content);
	}
	public function yieldSection()
	{
		return $this->yieldContent($this->stopSection());
	}
	public function stopSection($overwrite = false)
	{
		$last = array_pop($this->sectionStack);
		if ($overwrite)
		{
			$this->sections[$last] = ob_get_clean();
		}
		else
		{
			$this->extendSection($last, ob_get_clean());
		}
		return $last;
	}
	public function appendSection()
	{
		$last = array_pop($this->sectionStack);
		if (isset($this->sections[$last]))
		{
			$this->sections[$last] .= ob_get_clean();
		}
		else
		{
			$this->sections[$last] = ob_get_clean();
		}
		return $last;
	}
	protected function extendSection($section, $content)
	{
		if (isset($this->sections[$section]))
		{
			$content = str_replace('@parent', $content, $this->sections[$section]);
		}
		$this->sections[$section] = $content;
	}
	public function yieldContent($section, $default = '')
	{
		$sectionContent = $default;
		if (isset($this->sections[$section]))
		{
			$sectionContent = $this->sections[$section];
		}
		$sectionContent = str_replace('@@parent', '--parent--holder--', $sectionContent);
		return str_replace(
			'--parent--holder--', '@parent', str_replace('@parent', '', $sectionContent)
		);
	}
	public function flushSections()
	{
		$this->sections = array();
		$this->sectionStack = array();
	}
	public function flushSectionsIfDoneRendering()
	{
		if ($this->doneRendering()) $this->flushSections();
	}
	public function incrementRender()
	{
		$this->renderCount++;
	}
	public function decrementRender()
	{
		$this->renderCount--;
	}
	public function doneRendering()
	{
		return $this->renderCount == 0;
	}
	public function addLocation($location)
	{
		$this->finder->addLocation($location);
	}
	public function addNamespace($namespace, $hints)
	{
		$this->finder->addNamespace($namespace, $hints);
	}
	public function prependNamespace($namespace, $hints)
	{
		$this->finder->prependNamespace($namespace, $hints);
	}
	public function addExtension($extension, $engine, $resolver = null)
	{
		$this->finder->addExtension($extension);
		if (isset($resolver))
		{
			$this->engines->register($engine, $resolver);
		}
		unset($this->extensions[$extension]);
		$this->extensions = array_merge(array($extension => $engine), $this->extensions);
	}
	public function getExtensions()
	{
		return $this->extensions;
	}
	public function getEngineResolver()
	{
		return $this->engines;
	}
	public function getFinder()
	{
		return $this->finder;
	}
	public function setFinder(ViewFinderInterface $finder)
	{
		$this->finder = $finder;
	}
	public function getDispatcher()
	{
		return $this->events;
	}
	public function setDispatcher(Dispatcher $events)
	{
		$this->events = $events;
	}
	public function getContainer()
	{
		return $this->container;
	}
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}
	public function shared($key, $default = null)
	{
		return array_get($this->shared, $key, $default);
	}
	public function getShared()
	{
		return $this->shared;
	}
	public function hasSection($name)
	{
		return array_key_exists($name, $this->sections);
	}
	public function getSections()
	{
		return $this->sections;
	}
	public function getNames()
	{
		return $this->names;
	}
}
