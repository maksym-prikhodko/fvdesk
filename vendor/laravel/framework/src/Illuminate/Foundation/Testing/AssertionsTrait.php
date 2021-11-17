<?php namespace Illuminate\Foundation\Testing;
use Illuminate\View\View;
use PHPUnit_Framework_Assert as PHPUnit;
trait AssertionsTrait {
	public function assertResponseOk()
	{
		$actual = $this->response->getStatusCode();
		return PHPUnit::assertTrue($this->response->isOk(), "Expected status code 200, got {$actual}.");
	}
	public function assertResponseStatus($code)
	{
		$actual = $this->response->getStatusCode();
		return PHPUnit::assertEquals($code, $this->response->getStatusCode(), "Expected status code {$code}, got {$actual}.");
	}
	public function assertViewHas($key, $value = null)
	{
		if (is_array($key)) return $this->assertViewHasAll($key);
		if ( ! isset($this->response->original) || ! $this->response->original instanceof View)
		{
			return PHPUnit::assertTrue(false, 'The response was not a view.');
		}
		if (is_null($value))
		{
			PHPUnit::assertArrayHasKey($key, $this->response->original->getData());
		}
		else
		{
			PHPUnit::assertEquals($value, $this->response->original->$key);
		}
	}
	public function assertViewHasAll(array $bindings)
	{
		foreach ($bindings as $key => $value)
		{
			if (is_int($key))
			{
				$this->assertViewHas($value);
			}
			else
			{
				$this->assertViewHas($key, $value);
			}
		}
	}
	public function assertViewMissing($key)
	{
		if ( ! isset($this->response->original) || ! $this->response->original instanceof View)
		{
			return PHPUnit::assertTrue(false, 'The response was not a view.');
		}
		PHPUnit::assertArrayNotHasKey($key, $this->response->original->getData());
	}
	public function assertRedirectedTo($uri, $with = array())
	{
		PHPUnit::assertInstanceOf('Illuminate\Http\RedirectResponse', $this->response);
		PHPUnit::assertEquals($this->app['url']->to($uri), $this->response->headers->get('Location'));
		$this->assertSessionHasAll($with);
	}
	public function assertRedirectedToRoute($name, $parameters = array(), $with = array())
	{
		$this->assertRedirectedTo($this->app['url']->route($name, $parameters), $with);
	}
	public function assertRedirectedToAction($name, $parameters = array(), $with = array())
	{
		$this->assertRedirectedTo($this->app['url']->action($name, $parameters), $with);
	}
	public function assertSessionHas($key, $value = null)
	{
		if (is_array($key)) return $this->assertSessionHasAll($key);
		if (is_null($value))
		{
			PHPUnit::assertTrue($this->app['session.store']->has($key), "Session missing key: $key");
		}
		else
		{
			PHPUnit::assertEquals($value, $this->app['session.store']->get($key));
		}
	}
	public function assertSessionHasAll(array $bindings)
	{
		foreach ($bindings as $key => $value)
		{
			if (is_int($key))
			{
				$this->assertSessionHas($value);
			}
			else
			{
				$this->assertSessionHas($key, $value);
			}
		}
	}
	public function assertSessionHasErrors($bindings = array(), $format = null)
	{
		$this->assertSessionHas('errors');
		$bindings = (array) $bindings;
		$errors = $this->app['session.store']->get('errors');
		foreach ($bindings as $key => $value)
		{
			if (is_int($key))
			{
				PHPUnit::assertTrue($errors->has($value), "Session missing error: $value");
			}
			else
			{
				PHPUnit::assertContains($value, $errors->get($key, $format));
			}
		}
	}
	public function assertHasOldInput()
	{
		$this->assertSessionHas('_old_input');
	}
}
