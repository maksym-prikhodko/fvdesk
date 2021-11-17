<?php
class AccountController extends BaseController {
    public function getSignIn() {
        if (Config::get('database.install') == '1') {
            try {
                return View::make('themes/ep-admin/default1/account/signIn');
            } catch (Exception $e) {
                return Redirect::Route('account-sign-In');
            }
        } else {
            return Redirect::Route('licence');
        }
    }
    public function postSignIn() {
        $validator = Validator::make(Input::all(), array(
                    'email' => 'required|email',
                    'password' => 'required'
        ));
        if ($validator->fails()) {
            try {
                return Redirect::route('account-sign-In')
                                ->withErrors($validator)
                                ->withInput();
            } catch (Exception $e) {
                return Redirect::route('account-sign-In');
            }
        } 
        else {
            $remember = (Input::has('remember')) ? true : false;
            $auth = Auth::attempt(array(
                        'email' => Input::get('email'),
                        'password' => Input::get('password'),
                        'active' => 1,
                            ), $remember);
            if ($auth) {
                try {
                    return Redirect::route('npl');
                } catch (Exception $e) {
                    return Redirect::route('500');
                }
            } else {
                try {
                    return Redirect::route('account-sign-In')
                                    ->with('fails', Lang::get('lang.accountcontroller1'));
                } catch (Exception $e) {
                    return Redirect::route('account-sign-In');
                }
            }
        }
        try {
            return Redirect::route('account-sign-In')
                            ->with('fails', Lang::get('lang.accountcontroller2'));
        } catch (Exception $e) {
            return Redirect::route('account-sign-In');
        }
    }
    public function getSignOut() {
        Auth::logout();
        try {
            return Redirect::route('account-sign-In')->with('success', Lang::get('lang.accountcontroller3'));
        } catch (Exception $e) {
            return Redirect::route('account-sign-In')->with('success', Lang::get('lang.accountcontroller3'));
        }
    }
    public function getCreate() {
        if (Auth::check()) {
            if (Auth::user()->authority == "admin") {
                try {
                    return View::make('themes/ep-admin/default1/account.create');
                } catch (Exception $e) {
                    return View::make('401');
                }
            }
        }
        try {
            return Redirect::Route('401');
        } catch (Exception $e) {
            return Redirect::Route('401');
        }
    }
    public function postCreate() {
        $validator = Validator::make(Input::all(), array(
                    'email' => 'required|max:50|email|unique:users',
                    'username' => 'required|max:20|min:3|unique:users',
                    'password' => 'required|min:6',
                    'confirm_password' => 'required|same:password',
                    'authority' => 'required'
        ));
        if ($validator->fails()) {
            try {
                return View::make('/themes/ep-admin/default1/account.create')
                                ->withErrors($validator)
                                ->with('Input');
            } catch (Exception $e) {
                return Redirect::route('400');
            }
        }
        else {
            $email = Input::get('email');
            $username = Input::get('username');
            $password = Input::get('password');
            $authority = Input::get('authority');
            $code = str_random(60);
            $user = User::create(array(
                        'email' => $email,
                        'username' => $username,
                        'password' => Hash::make($password),
                        'authority' => $authority,
                        'code' => $code,
                        'active' => 0
            ));
            if ($user) {
                try {
                    Mail::send('/themes/ep-admin/default1/emails.auth.activate', array('link' => URL::route('account-activate', $code), 'username' => $username), function($message) use($user) {
                        $message->to($user->email, $user->username)->subject('active your account');
                    });
                } catch (Exception $e) {
                    return Redirect::route('account-create')->with('fails', Lang::get('lang.accountcontroller4'));
                }
                try {
                    return Redirect::route('account-create')->with('success', Lang::get('lang.accountcontroller5'));
                } catch (Exception $e) {
                    return Redirect::route('400');
                }
            }
        }
    }
    public function getActivate($code) {
        $user = User::where('code', '=', $code)->where('active', '=', 0);
        if ($user->count()) {
            $user = $user->first();
            $user->active = 1;
            $user->code = '';
            if ($user->save()) {
                try {
                    return Redirect::route('home')
                                    ->with('success', Lang::get('lang.accountcontroller6'));
                } catch (Exception $e) {
                    return Redirect::route('home')->with('success', Lang::get('lang.accountcontroller6'));
                }
            }
        }
        try {
            return Redirect::route('home')
                            ->with('fails', Lang::get('lang.accountcontroller7'));
        } catch (Exception $e) {
            return Redirect::route('home')
                            ->with('fails', Lang::get('lang.accountcontroller7'));
        }
    }
    public function getChangePassword() {
        try {
            return View::make('themes/ep-admin/default1/account.password');
        } catch (Exception $e) {
            return View::make('themes/ep-admin/default1/account.password');
        }
    }
    public function postChangePassword() {
        $validator = Validator::make(Input::all(), array(
                    'old_password' => 'required',
                    'password' => 'required|min:6',
                    'confirm_password' => 'required|same:password'
        ));
        if ($validator->fails()) {
            try {
                return Redirect::route('profile')
                                ->withErrors($validator);
            } catch (Exception $e) {
                return Redirect::route('profile')
                                ->withErrors($validator);
            }
        }
        else {
            $user = User::find(Auth::user()->id);
            $old_password = Input::get('old_password');
            $password = Input::get('password');
            if (Hash::check($old_password, $user->getAuthPassword())) {
                $user->password = Hash::make($password);
                if ($user->save()) {
                    try {
                        return Redirect::route('profile')
                                        ->with('success1', Lang::get('lang.accountcontroller8'));
                    } catch (Exception $e) {
                        return Redirect::route('400');
                    }
                }
            } 
            else {
                try {
                    return Redirect::route('profile')
                                    ->with('fails1', Lang::get('lang.accountcontroller9'));
                } catch (Exception $e) {
                    return Redirect::route('500');
                }
            }
        }
        try {
            return Redirect::route('profile')
                            ->with('fails1', Lang::get('lang.accountcontroller10'));
        } catch (Exception $e) {
            return Redirect::route('500');
        }
    }
    public function getForgotPassword() {
        try {
            return View::make('themes/ep-admin/default1/account.forgot');
        } catch (Exception $e) {
            return View::make('themes/ep-admin/default1/account.forgot');
        }
    }
    public function postForgotPassword() {
        $validator = Validator::make(Input::all(), array(
                    'email' => 'required|email'
        ));
        if ($validator->fails()) {
            try {
                return Redirect::route('account-forgot-password')
                                ->withErrors($validator)
                                ->withInput();
            } catch (Exception $e) {
                return Redirect::route('account-forgot-password');
            }
        } else {
            $user = User::where('email', '=', Input::get('email'));
            if ($user->count()) {
                $user = $user->first();
                $code = str_random(60);
                $password = str_random(10);
                $user->code = $code;
                $user->password_temp = Hash::make($password);
                if ($user->save()) {
                    try {
                        Mail::send('themes/ep-admin/default1/emails.auth.forgot', array('link' => URL::route('account-recover', $code), 'username' => $user->username, 'password' => $password), function($message) use($user) {
                            $message->to($user->email, $user->username)->subject('your new password');
                        });
                    } catch (Exception $e) {
                        return Redirect::route('account-forgot-password')
                                        ->with('fails', Lang::get('lang.accountcontroller11'));
                    }
                    try {
                        return Redirect::route('account-forgot-password')
                                        ->with('success', Lang::get('lang.accountcontroller12'));
                    } catch (Exception $e) {
                        return Redirect::route('500')
                                        ->with('success', Lang::get('lang.accountcontroller12'));
                    }
                }
            }
        }
        try {
            return Redirect::route('account-forgot-password')
                            ->with('fails', Lang::get('lang.accountcontroller13'));
        } catch (Exception $e) {
            return Redirect::route('account-forgot-password')
                            ->with('fails', Lang::get('lang.accountcontroller13'));
        }
    }
    public function getRecover($code) {
        $user = User::where('code', '=', $code)
                ->where('password_temp', '!=', '');
        if ($user->count()) {
            $user = $user->first();
            $user->password = $user->password_temp;
            $user->password_temp = '';
            $user->code = '';
            if ($user->save()) {
                try {
                    return Redirect::route('home')
                                    ->with('success', Lang::get('lang.accountcontroller14'));
                } catch (Exception $e) {
                    return Redirect::route('home')
                                    ->with('success', Lang::get('lang.accountcontroller14'));
                }
            }
        }
        try {
            return Redirect::route('home')
                            ->with('fails', Lang::get('lang.accountcontroller15'));
        } catch (Exception $e) {
            return Redirect::route('home')
                            ->with('fails', Lang::get('lang.accountcontroller15'));
        }
    }
    public function getprofile() {
        $user = User::find(Auth::user()->id);
        return View::make('themes/ep-admin/default1/account/profile', compact('user'));
    }
    public function postprofile() {
        $validator = Validator::make(Input::all(), array(
                    'username' => 'required|min:3|max:30',
        ));
        if ($validator->fails()) {
            return Redirect::route('profile')
                            ->withErrors($validator);
        } else {
            $user = User::find(Auth::user()->id);
            $user->firstname = Input::get('firstname');
            $user->lastname = Input::get('lastname');
            $user->username = Input::get('username');
            $user->gender = Input::get('gender');
            $user->phone = Input::get('phoneno');
            $user->organisation = Input::get('org');
            $user->save();
            return Redirect::route('profile')->with('success', Lang::get('lang.accountcontroller16'));
        }
    }
    public function userlist() {
        $User = User::all();
        return View::make('themes/ep-admin/default1/account/list', compact('User'));
    }
    public function edit(User $user) {
        return View::make('themes/ep-admin/default1/account/edit', compact('user'));
    }
    public function postedit() {
        $validator = Validator::make(Input::all(), array(
                    'username' => 'required',
                    'authority' => 'required'
        ));
        if ($validator->fails()) {
            return Redirect::route('user.edit');
        } else {
            $user = User::findorfail(Input::get('userid'));
            $user->username = Input::get('username');
            $user->authority = Input::get('authority');
            $user->active = Input::get('active');
            $user->save();
            return Redirect::route('userlist');
        }
    }
    public function delete(User $user) {
        return View::make('themes/ep-admin/default1/account/delete', compact('user'));
    }
    public function postdelete() {
        $user = User::find(Input::get('userid'));
        $user->delete();
        return Redirect::route('userlist');
    }
    public function getadminchangepassword(User $user) {
        return View::make('themes/ep-admin/default1/admin/resetpassword', compact('user'));
    }
    public function postadminchangepassword() {
        $validator = Validator::make(Input::all(), array(
                    'password' => 'required|min:6',
                    'confirmpassword' => 'required|same:password'
        ));
        if ($validator->fails()) {
            return Redirect::route('getreset', Input::get('userid'))
                            ->withErrors($validator)
                            ->withInput();
        } else {
            $user = User::findorfail(Input::get('userid'));
            $password = Input::get('password');
            $user->password = Hash::make($password);
            if ($user->save()) {
                return Redirect::route('getreset', Input::get('userid'))->with('success', Lang::get('lang.accountcontroller17'));
            } else {
                return Redirect::route('getreset', Input::get('userid'))->with('fails', Lang::get('lang.accountcontroller18'));
            }
        }
    }
}
