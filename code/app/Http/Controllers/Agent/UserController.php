<?php namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfilePassword;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\Sys_userRequest;
use App\Http\Requests\Sys_userUpdate;
use App\Model\Agent_panel\Sys_user;
use App\User;
use Auth;
use Hash;
use Input;
class UserController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('role.agent');
		$this->middleware('roles');
	}
	public function index(Sys_user $user) {
		try
		{
			$users = $user->get();
			return view('themes.default1.agent.user.index', compact('users'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function create() {
		try
		{
			return view('themes.default1.agent.user.create');
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function store(Sys_user $user, Sys_userRequest $request) {
		try
		{
			if ($user->fill($request->input())->save() == true) {
				return redirect('user')->with('success', 'User  Created Successfully');
			} else {
				return redirect('user')->with('fails', 'User  can not Create');
			}
		} catch (Exception $e) {
			return redirect('user')->with('fails', 'User  can not Create');
		}
	}
	public function show($id, Sys_user $user) {
		try
		{
			$users = $user->whereId($id)->first();
			return view('themes.default1.agent.user.show', compact('users'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function edit($id, Sys_user $user) {
		try
		{
			$users = $user->whereId($id)->first();
			return view('themes.default1.agent.user.edit', compact('users'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function update($id, Sys_user $user, Sys_userUpdate $request) {
		try
		{
			$users = $user->whereId($id)->first();
			if ($users->fill($request->input())->save() == true) {
				return redirect('user')->with('success', 'User  Updated Successfully');
			} else {
				return redirect('user')->with('fails', 'User  can not Update');
			}
		} catch (Exception $e) {
			return redirect('user')->with('fails', 'User  can not Update');
		}
	}
	public function destroy($id, Sys_user $user) {
		try
		{
			$users = $user->whereId($id)->first();
			if ($users->delete() == true) {
				return redirect('user')->with('success', 'User  Deleted Successfully');
			} else {
				return redirect('user')->with('fails', 'User  can not Delete');
			}
		} catch (Exception $e) {
			return redirect('user')->with('fails', 'User  can not Delete');
		}
	}
	public function getProfile() {
		$user = Auth::user();
		return view('themes.default1.agent.user.profile', compact('user'));
	}
	public function postProfile($id, ProfileRequest $request) {
		$user = Auth::user();
		$user->gender = $request->input('gender');
		$user->save();
		if ($user->profile_pic == 'avatar5.png' || $user->profile_pic == 'avatar2.png') {
			if ($request->input('gender') == 1) {
				$name = 'avatar5.png';
				$destinationPath = 'dist/img';
				$user->profile_pic = $name;
			} elseif ($request->input('gender') == 0) {
				$name = 'avatar2.png';
				$destinationPath = 'dist/img';
				$user->profile_pic = $name;
			}
		}
		if (Input::file('profile_pic')) {
			$name = Input::file('profile_pic')->getClientOriginalName();
			$destinationPath = 'dist/img';
			$fileName = rand(0000, 9999) . '.' . $name;
			Input::file('profile_pic')->move($destinationPath, $fileName);
			$user->profile_pic = $fileName;
		} else {
			$user->fill($request->except('profile_pic', 'gender'))->save();
			return redirect('guest')->with('success', 'Profile Updated sucessfully');
		}
		if ($user->fill($request->except('profile_pic'))->save()) {
			return redirect('guest')->with('success', 'Profile Updated sucessfully');
		}
	}
	public function postProfilePassword($id, ProfilePassword $request) {
		$user = Auth::user();
		if (Hash::check($request->input('old_password'), $user->getAuthPassword())) {
			$user->password = Hash::make($request->input('new_password'));
			$user->save();
			return redirect('guest')->with('success', 'Password Updated sucessfully');
		} else {
			return redirect('guest')->with('fails', 'Password was not Updated');
		}
	}
}
