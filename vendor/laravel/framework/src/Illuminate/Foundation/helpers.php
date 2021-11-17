<?php
use Illuminate\Support\Str;
use Illuminate\Container\Container;
if ( ! function_exists('abort'))
{
	function abort($code, $message = '', array $headers = array())
	{
		return app()->abort($code, $message, $headers);
	}
}
if ( ! function_exists('action'))
{
	function action($name, $parameters = array())
	{
		return app('url')->action($name, $parameters);
	}
}
if ( ! function_exists('app'))
{
	function app($make = null, $parameters = [])
	{
		if (is_null($make)) return Container::getInstance();
		return Container::getInstance()->make($make, $parameters);
	}
}
if ( ! function_exists('app_path'))
{
	function app_path($path = '')
	{
		return app('path').($path ? '/'.$path : $path);
	}
}
if ( ! function_exists('asset'))
{
	function asset($path, $secure = null)
	{
		return app('url')->asset($path, $secure);
	}
}
if ( ! function_exists('auth'))
{
	function auth()
	{
		return app('Illuminate\Contracts\Auth\Guard');
	}
}
if ( ! function_exists('base_path'))
{
	function base_path($path = '')
	{
		return app()->basePath().($path ? '/'.$path : $path);
	}
}
if ( ! function_exists('back'))
{
	function back($status = 302, $headers = array())
	{
		return app('redirect')->back($status, $headers);
	}
}
if ( ! function_exists('bcrypt'))
{
	function bcrypt($value, $options = array())
	{
		return app('hash')->make($value, $options);
	}
}
if ( ! function_exists('config'))
{
	function config($key = null, $default = null)
	{
		if (is_null($key)) return app('config');
		if (is_array($key))
		{
			return app('config')->set($key);
		}
		return app('config')->get($key, $default);
	}
}
if ( ! function_exists('config_path'))
{
	function config_path($path = '')
	{
		return app()->make('path.config').($path ? '/'.$path : $path);
	}
}
if ( ! function_exists('cookie'))
{
	function cookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
	{
		$cookie = app('Illuminate\Contracts\Cookie\Factory');
		if (is_null($name))
		{
			return $cookie;
		}
		return $cookie->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly);
	}
}
if ( ! function_exists('csrf_token'))
{
	function csrf_token()
	{
		$session = app('session');
		if (isset($session))
		{
			return $session->getToken();
		}
		throw new RuntimeException("Application session store not set.");
	}
}
if ( ! function_exists('database_path'))
{
	function database_path($path = '')
	{
		return app()->databasePath().($path ? '/'.$path : $path);
	}
}
if ( ! function_exists('delete'))
{
	function delete($uri, $action)
	{
		return app('router')->delete($uri, $action);
	}
}
if ( ! function_exists('get'))
{
	function get($uri, $action)
	{
		return app('router')->get($uri, $action);
	}
}
if ( ! function_exists('info'))
{
	function info($message, $context = array())
	{
		return app('log')->info($message, $context);
	}
}
if ( ! function_exists('logger'))
{
	function logger($message = null, array $context = array())
	{
		if (is_null($message)) return app('log');
		return app('log')->debug($message, $context);
	}
}
if ( ! function_exists('old'))
{
	function old($key = null, $default = null)
	{
		return app('request')->old($key, $default);
	}
}
if ( ! function_exists('patch'))
{
	function patch($uri, $action)
	{
		return app('router')->patch($uri, $action);
	}
}
if ( ! function_exists('post'))
{
	function post($uri, $action)
	{
		return app('router')->post($uri, $action);
	}
}
if ( ! function_exists('put'))
{
	function put($uri, $action)
	{
		return app('router')->put($uri, $action);
	}
}
if ( ! function_exists('public_path'))
{
	function public_path($path = '')
	{
		return app()->make('path.public').($path ? '/'.$path : $path);
	}
}
if ( ! function_exists('redirect'))
{
	function redirect($to = null, $status = 302, $headers = array(), $secure = null)
	{
		if (is_null($to)) return app('redirect');
		return app('redirect')->to($to, $status, $headers, $secure);
	}
}
if ( ! function_exists('resource'))
{
	function resource($name, $controller, array $options = [])
	{
		return app('router')->resource($name, $controller, $options);
	}
}
if ( ! function_exists('response'))
{
	function response($content = '', $status = 200, array $headers = array())
	{
		$factory = app('Illuminate\Contracts\Routing\ResponseFactory');
		if (func_num_args() === 0)
		{
			return $factory;
		}
		return $factory->make($content, $status, $headers);
	}
}
if ( ! function_exists('route'))
{
	function route($name, $parameters = array(), $absolute = true, $route = null)
	{
		return app('url')->route($name, $parameters, $absolute, $route);
	}
}
if ( ! function_exists('secure_asset'))
{
	function secure_asset($path)
	{
		return asset($path, true);
	}
}
if ( ! function_exists('secure_url'))
{
	function secure_url($path, $parameters = array())
	{
		return url($path, $parameters, true);
	}
}
if ( ! function_exists('session'))
{
	function session($key = null, $default = null)
	{
		if (is_null($key)) return app('session');
		if (is_array($key)) return app('session')->put($key);
		return app('session')->get($key, $default);
	}
}
if ( ! function_exists('storage_path'))
{
	function storage_path($path = '')
	{
		return app('path.storage').($path ? '/'.$path : $path);
	}
}
if ( ! function_exists('trans'))
{
	function trans($id = null, $parameters = array(), $domain = 'messages', $locale = null)
	{
		if (is_null($id)) return app('translator');
		return app('translator')->trans($id, $parameters, $domain, $locale);
	}
}
if ( ! function_exists('trans_choice'))
{
	function trans_choice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
	{
		return app('translator')->transChoice($id, $number, $parameters, $domain, $locale);
	}
}
if ( ! function_exists('url'))
{
	function url($path = null, $parameters = array(), $secure = null)
	{
		return app('Illuminate\Contracts\Routing\UrlGenerator')->to($path, $parameters, $secure);
	}
}
if ( ! function_exists('view'))
{
	function view($view = null, $data = array(), $mergeData = array())
	{
		$factory = app('Illuminate\Contracts\View\Factory');
		if (func_num_args() === 0)
		{
			return $factory;
		}
		return $factory->make($view, $data, $mergeData);
	}
}
if ( ! function_exists('env'))
{
	function env($key, $default = null)
	{
		$value = getenv($key);
		if ($value === false) return value($default);
		switch (strtolower($value))
		{
			case 'true':
			case '(true)':
				return true;
			case 'false':
			case '(false)':
				return false;
			case 'empty':
			case '(empty)':
				return '';
			case 'null':
			case '(null)':
				return;
		}
		if (Str::startsWith($value, '"') && Str::endsWith($value, '"'))
		{
			return substr($value, 1, -1);
		}
		return $value;
	}
}
if ( ! function_exists('event'))
{
	function event($event, $payload = array(), $halt = false)
	{
		return app('events')->fire($event, $payload, $halt);
	}
}
if ( ! function_exists('elixir'))
{
	function elixir($file)
	{
		static $manifest = null;
		if (is_null($manifest))
		{
			$manifest = json_decode(file_get_contents(public_path().'/build/rev-manifest.json'), true);
		}
		if (isset($manifest[$file]))
		{
			return '/build/'.$manifest[$file];
		}
		throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
	}
}
