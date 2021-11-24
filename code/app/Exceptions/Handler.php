<?php namespace App\Exceptions;
use Exception;
use Bugsnag\BugsnagLaravel\BugsnagExceptionHandler as ExceptionHandler;
class Handler extends ExceptionHandler {
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException',
	];
	public function report(Exception $e) {
		return parent::report($e);
	}
	public function render($request, Exception $e) {
		if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
			return redirect('404');
		} elseif ($e instanceof \Illuminate\View\Engines\handleViewException) {
			return redirect('404');
		} elseif ($e instanceof \Illuminate\Database\QueryException) {
			return redirect('404');
		} elseif ($e) {
			return redirect('404');
		}
		return parent::render($request, $e);
	}
}
