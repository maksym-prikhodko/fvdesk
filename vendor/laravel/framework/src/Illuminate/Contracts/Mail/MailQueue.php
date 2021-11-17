<?php namespace Illuminate\Contracts\Mail;
interface MailQueue {
	public function queue($view, array $data, $callback, $queue = null);
	public function later($delay, $view, array $data, $callback, $queue = null);
}
