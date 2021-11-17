<?php namespace Illuminate\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface as BaseSessionInterface;
interface SessionInterface extends BaseSessionInterface {
	public function getHandler();
	public function handlerNeedsRequest();
	public function setRequestOnHandler(Request $request);
}
