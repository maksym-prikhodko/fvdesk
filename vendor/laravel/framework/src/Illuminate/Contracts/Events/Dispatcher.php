<?php namespace Illuminate\Contracts\Events;
interface Dispatcher {
	public function listen($events, $listener, $priority = 0);
	public function hasListeners($eventName);
	public function until($event, $payload = array());
	public function fire($event, $payload = array(), $halt = false);
	public function firing();
	public function forget($event);
	public function forgetPushed();
}
