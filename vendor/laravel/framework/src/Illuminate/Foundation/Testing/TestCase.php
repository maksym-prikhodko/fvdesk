<?php namespace Illuminate\Foundation\Testing;
use PHPUnit_Framework_TestCase;
abstract class TestCase extends PHPUnit_Framework_TestCase {
	use ApplicationTrait, AssertionsTrait;
	abstract public function createApplication();
	public function setUp()
	{
		if ( ! $this->app)
		{
			$this->refreshApplication();
		}
	}
	public function tearDown()
	{
		if ($this->app)
		{
			$this->app->flush();
		}
	}
}
