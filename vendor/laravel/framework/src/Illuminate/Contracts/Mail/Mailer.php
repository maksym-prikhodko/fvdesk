<?php namespace Illuminate\Contracts\Mail;
interface Mailer {
	public function raw($text, $callback);
	public function send($view, array $data, $callback);
	public function failures();
}
