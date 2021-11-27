<?php
namespace App\Http\Controllers\Error;
use App\Http\Controllers\Controller;
class ErrorController extends Controller {
	public function error404() {
		return view('404');
	}
}
