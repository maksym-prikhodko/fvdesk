<?php namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use Hash;
use Mail;
use App\Http\Requests\LoginRequest;
class AuthController extends Controller {
	use AuthenticatesAndRegistersUsers;
	protected $redirectTo = '/';
	protected $redirectAfterLogout = '/';
	protected $loginPath = '/auth/login';
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
		$this->registrar = $registrar;
		$this->middleware('guest', ['except' => 'getLogout']);
	}
	public function getRegister()
    {
        return view('auth.register');
    }
    public function postRegister(User $user, RegisterRequest $request)
    {
        $password = Hash::make($request->input('password'));
        $user->password = $password;
        $name = $request->input('full_name');
        $user->name = $name;
        $user->email = $request->input('email');
        $user->role = 'user';
        $code = str_random(60);
		$user->remember_token = $code;
		$user->save();
		$mail =  Mail::send('auth.activate',  array('link' => url('getmail', $code), 'username' => $name), function($message) use($user) {
                        $message->to($user->email, $user->full_name)->subject('active your account');
                    });
		return redirect('guest')->with('success','Activate Your Account ! Click on Link that send to your mail');
    }
    public function getMail($token, User $user)
    {
    	$user  = $user->where('remember_token',$token)->where('active',0)->first();
    	if($user)
    	{
	    	$user->active = 1;
	    	$user->save();
	    	return redirect('auth/login');
	    }
	    else
	    {
	    	return redirect('auth/login');
	    }
    }
    public function getLogin()
	{
		return view('auth.login');
	}
	public function postLogin(LoginRequest $request )
	{
		$credentials = $request->only('email', 'password');
		if ($this->auth->attempt($credentials, $request->has('remember')))
		{
			return redirect()->intended($this->redirectPath());
		}
		return redirect($this->loginPath())
					->withInput($request->only('email', 'remember'))
					->withErrors([
						'email' => $this->getFailedLoginMessage(),
						'password'=>$this->getFailedLoginMessage()
					]);
	}
	protected function getFailedLoginMessage()
	{
		return 'This Field do not match our records.';
	}
}
