<?php namespace Illuminate\Foundation\Auth;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
trait AuthenticatesAndRegistersUsers {
	protected $auth;
	protected $registrar;
	public function getRegister()
	{
		return view('auth.register');
	}
	public function postRegister(Request $request)
	{
		$validator = $this->registrar->validator($request->all());
		if ($validator->fails())
		{
			$this->throwValidationException(
				$request, $validator
			);
		}
		$this->auth->login($this->registrar->create($request->all()));
		return redirect($this->redirectPath());
	}
	public function getLogin()
	{
		return view('auth.login');
	}
	public function postLogin(Request $request)
	{
		$this->validate($request, [
			'email' => 'required|email', 'password' => 'required',
		]);
		$credentials = $request->only('email', 'password');
		if ($this->auth->attempt($credentials, $request->has('remember')))
		{
			return redirect()->intended($this->redirectPath());
		}
		return redirect($this->loginPath())
					->withInput($request->only('email', 'remember'))
					->withErrors([
						'email' => $this->getFailedLoginMessage(),
					]);
	}
	protected function getFailedLoginMessage()
	{
		return 'These credentials do not match our records.';
	}
	public function getLogout()
	{
		$this->auth->logout();
		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
	}
	public function redirectPath()
	{
		if (property_exists($this, 'redirectPath'))
		{
			return $this->redirectPath;
		}
		return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
	}
	public function loginPath()
	{
		return property_exists($this, 'loginPath') ? $this->loginPath : '/auth/login';
	}
}
