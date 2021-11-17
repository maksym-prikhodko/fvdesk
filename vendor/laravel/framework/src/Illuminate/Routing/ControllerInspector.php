<?php namespace Illuminate\Routing;
use ReflectionClass;
use ReflectionMethod;
class ControllerInspector {
	protected $verbs = array(
		'any', 'get', 'post', 'put', 'patch',
		'delete', 'head', 'options',
	);
	public function getRoutable($controller, $prefix)
	{
		$routable = array();
		$reflection = new ReflectionClass($controller);
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method)
		{
			if ($this->isRoutable($method))
			{
				$data = $this->getMethodData($method, $prefix);
				$routable[$method->name][] = $data;
				if ($data['plain'] == $prefix.'/index')
				{
					$routable[$method->name][] = $this->getIndexData($data, $prefix);
				}
			}
		}
		return $routable;
	}
	public function isRoutable(ReflectionMethod $method)
	{
		if ($method->class == 'Illuminate\Routing\Controller') return false;
		return starts_with($method->name, $this->verbs);
	}
	public function getMethodData(ReflectionMethod $method, $prefix)
	{
		$verb = $this->getVerb($name = $method->name);
		$uri = $this->addUriWildcards($plain = $this->getPlainUri($name, $prefix));
		return compact('verb', 'plain', 'uri');
	}
	protected function getIndexData($data, $prefix)
	{
		return array('verb' => $data['verb'], 'plain' => $prefix, 'uri' => $prefix);
	}
	public function getVerb($name)
	{
		return head(explode('_', snake_case($name)));
	}
	public function getPlainUri($name, $prefix)
	{
		return $prefix.'/'.implode('-', array_slice(explode('_', snake_case($name)), 1));
	}
	public function addUriWildcards($uri)
	{
		return $uri.'/{one?}/{two?}/{three?}/{four?}/{five?}';
	}
}
