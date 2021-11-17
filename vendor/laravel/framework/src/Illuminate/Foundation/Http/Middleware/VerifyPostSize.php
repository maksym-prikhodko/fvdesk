<?php namespace Illuminate\Foundation\Http\Middleware;
use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\Exception\PostTooLargeException;
class VerifyPostSize implements Middleware {
	public function handle($request, Closure $next)
	{
		if ($request->server('CONTENT_LENGTH') > $this->getPostMaxSize())
		{
			throw new PostTooLargeException;
		}
		return $next($request);
	}
   	protected function getPostMaxSize()
   	{
		$postMaxSize = ini_get('post_max_size');
		switch (substr($postMaxSize, -1))
		{
			case 'M':
			case 'm':
				return (int) $postMaxSize * 1048576;
			case 'K':
			case 'k':
				return (int) $postMaxSize * 1024;
			case 'G':
			case 'g':
				return (int) $postMaxSize * 1073741824;
		}
		return (int) $postMaxSize;
	}
}
