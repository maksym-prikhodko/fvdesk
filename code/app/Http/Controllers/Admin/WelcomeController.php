<?php namespace App\Http\Controllers\Admin;
class WelcomeController extends Controller {
	public function __construct()
	{
		$this->middleware('guest');
	}
	public function index()
	{
		return view('welcome');
	}
}
