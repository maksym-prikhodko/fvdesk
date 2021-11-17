<?php namespace Illuminate\Routing;
class ResourceRegistrar {
	protected $router;
	protected $resourceDefaults = array('index', 'create', 'store', 'show', 'edit', 'update', 'destroy');
	public function __construct(Router $router)
	{
		$this->router = $router;
	}
	public function register($name, $controller, array $options = array())
	{
		if (str_contains($name, '/'))
		{
			$this->prefixedResource($name, $controller, $options);
			return;
		}
		$base = $this->getResourceWildcard(last(explode('.', $name)));
		$defaults = $this->resourceDefaults;
		foreach ($this->getResourceMethods($defaults, $options) as $m)
		{
			$this->{'addResource'.ucfirst($m)}($name, $base, $controller, $options);
		}
	}
	protected function prefixedResource($name, $controller, array $options)
	{
		list($name, $prefix) = $this->getResourcePrefix($name);
		$callback = function($me) use ($name, $controller, $options)
		{
			$me->resource($name, $controller, $options);
		};
		return $this->router->group(compact('prefix'), $callback);
	}
	protected function getResourcePrefix($name)
	{
		$segments = explode('/', $name);
		$prefix = implode('/', array_slice($segments, 0, -1));
		return array(end($segments), $prefix);
	}
	protected function getResourceMethods($defaults, $options)
	{
		if (isset($options['only']))
		{
			return array_intersect($defaults, (array) $options['only']);
		}
		elseif (isset($options['except']))
		{
			return array_diff($defaults, (array) $options['except']);
		}
		return $defaults;
	}
	public function getResourceUri($resource)
	{
		if ( ! str_contains($resource, '.')) return $resource;
		$segments = explode('.', $resource);
		$uri = $this->getNestedResourceUri($segments);
		return str_replace('/{'.$this->getResourceWildcard(last($segments)).'}', '', $uri);
	}
	protected function getNestedResourceUri(array $segments)
	{
		return implode('/', array_map(function($s)
		{
			return $s.'/{'.$this->getResourceWildcard($s).'}';
		}, $segments));
	}
	protected function getResourceAction($resource, $controller, $method, $options)
	{
		$name = $this->getResourceName($resource, $method, $options);
		return array('as' => $name, 'uses' => $controller.'@'.$method);
	}
	protected function getResourceName($resource, $method, $options)
	{
		if (isset($options['names'][$method])) return $options['names'][$method];
		$prefix = isset($options['as']) ? $options['as'].'.' : '';
		if ( ! $this->router->hasGroupStack())
		{
			return $prefix.$resource.'.'.$method;
		}
		return $this->getGroupResourceName($prefix, $resource, $method);
	}
	protected function getGroupResourceName($prefix, $resource, $method)
	{
		$group = trim(str_replace('/', '.', $this->router->getLastGroupPrefix()), '.');
		if (empty($group))
		{
			return trim("{$prefix}{$resource}.{$method}", '.');
		}
		return trim("{$prefix}{$group}.{$resource}.{$method}", '.');
	}
	public function getResourceWildcard($value)
	{
		return str_replace('-', '_', $value);
	}
	protected function addResourceIndex($name, $base, $controller, $options)
	{
		$uri = $this->getResourceUri($name);
		$action = $this->getResourceAction($name, $controller, 'index', $options);
		return $this->router->get($uri, $action);
	}
	protected function addResourceCreate($name, $base, $controller, $options)
	{
		$uri = $this->getResourceUri($name).'/create';
		$action = $this->getResourceAction($name, $controller, 'create', $options);
		return $this->router->get($uri, $action);
	}
	protected function addResourceStore($name, $base, $controller, $options)
	{
		$uri = $this->getResourceUri($name);
		$action = $this->getResourceAction($name, $controller, 'store', $options);
		return $this->router->post($uri, $action);
	}
	protected function addResourceShow($name, $base, $controller, $options)
	{
		$uri = $this->getResourceUri($name).'/{'.$base.'}';
		$action = $this->getResourceAction($name, $controller, 'show', $options);
		return $this->router->get($uri, $action);
	}
	protected function addResourceEdit($name, $base, $controller, $options)
	{
		$uri = $this->getResourceUri($name).'/{'.$base.'}/edit';
		$action = $this->getResourceAction($name, $controller, 'edit', $options);
		return $this->router->get($uri, $action);
	}
	protected function addResourceUpdate($name, $base, $controller, $options)
	{
		$this->addPutResourceUpdate($name, $base, $controller, $options);
		return $this->addPatchResourceUpdate($name, $base, $controller);
	}
	protected function addPutResourceUpdate($name, $base, $controller, $options)
	{
		$uri = $this->getResourceUri($name).'/{'.$base.'}';
		$action = $this->getResourceAction($name, $controller, 'update', $options);
		return $this->router->put($uri, $action);
	}
	protected function addPatchResourceUpdate($name, $base, $controller)
	{
		$uri = $this->getResourceUri($name).'/{'.$base.'}';
		$this->router->patch($uri, $controller.'@update');
	}
	protected function addResourceDestroy($name, $base, $controller, $options)
	{
		$uri = $this->getResourceUri($name).'/{'.$base.'}';
		$action = $this->getResourceAction($name, $controller, 'destroy', $options);
		return $this->router->delete($uri, $action);
	}
}
