<?php namespace App\Http\Controllers\Admin;
use App\Http\Requests\BanRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\BanlistRequest;
use Illuminate\Http\Request;
use App\Model\Email\Banlist;
use App\User;
class BanlistController extends Controller {
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Banlist $ban)
	{
		try
		{
			$bans = $ban->get();
			return view('themes.default1.admin.emails.banlist.index',compact('bans'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function create()
	{
		try
		{
			return view('themes.default1.admin.emails.banlist.create');
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function store(banlist $ban, BanRequest $request, User $user)
	{
		try
		{
			$adban = $request->input('email_address');
			$use = $user->where('email',$adban)->first();
			if($use!==null)
			{
				$use->ban = 1;
				$use->save();
				$ban->create($request->input())->save();
				return redirect('banlist')->with('success','Email Banned sucessfully');
			}
			else
			{
				$ban->create($request->input())->save();
				return redirect('banlist')->with('success','Email Banned sucessfully');
			}
		}
		catch(Exception $e)
		{
			return redirect('banlist')->with('fails','Email can not Ban');
		}
	}
	public function show($id)
	{
	}
	public function edit($id, Banlist $ban)
	{
		try
		{
			$bans = $ban->whereId($id)->first();
			return view('themes.default1.admin.emails.banlist.edit',compact('bans'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function update($id, Banlist $ban, BanlistRequest $request)
	{
		try
		{
			$bans = $ban->whereId($id)->first();
			if($bans->fill($request->input())->save())
			{
				return redirect('banlist')->with('success','Banned Email Updated sucessfully');
			}
			else
			{
				return redirect('banlist')->with('fails','Banned Email not Updated');
			}
		}
		catch(Exception $e)
		{
			return redirect('banlist')->with('fails','Banned Email not Updated');
		}
	}
	public function destroy($id, Banlist $ban)
	{
		try
		{
			$bans = $ban->whereId($id)->first();
			if($bans->delete()==true)
			{
				return redirect('banlist')->with('success','Banned Email Deleted sucessfully');
			}
			else
			{
				return redirect('banlist')->with('fails','Banned Email can not Delete');
			}
		}
		catch(Exception $e)
		{
			return redirect('banlist')->with('fails','Banned Email can not Delete');
		}
	}
}
