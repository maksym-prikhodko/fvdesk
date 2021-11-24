<?php namespace App\Http\Controllers\Admin;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Manage\Sla_plan;
use App\Http\Requests\SlaRequest;
use App\Http\Requests\SlaUpdate;
class SlaController extends Controller {
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Sla_plan $sla)
	{
		try
		{
			$slas = $sla->get();
			return view('themes.default1.admin.manage.sla.index',compact('slas'));
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
			return view('themes.default1.admin.manage.sla.create');
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function store(Sla_plan $sla, SlaRequest $request)
	{
		try
		{
			if($sla->fill($request->input())->save()==true)
			{
				return redirect('sla')->with('success','SLA Plan Created Successfully');
			}
			else
			{
				return redirect('sla')->with('fails','SLA Plan can not Create');	
			}
		}		
		catch(Exception $e)
		{
			return redirect('sla')->with('fails','SLA Plan can not Create');
		}
	}
	public function show($id)
	{
	}
	public function edit($id, Sla_plan $sla)
	{
		try
		{
			$slas = $sla->whereId($id)->first();
			$slas->get();
			return view('themes.default1.admin.manage.sla.edit',compact('slas'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function update($id, Sla_plan $sla, SlaUpdate $request)
	{
		try
		{
			$slas = $sla->whereId($id)->first();
			$slas->fill($request->except('transient','ticket_overdue'))->save();
			$slas->transient=$request->input('transient');
			$slas->ticket_overdue=$request->input('ticket_overdue');
			if($slas->save()==true)
			{
				return redirect('sla')->with('success','SLA Plan Updated Successfully');
			}
			else
			{
				return redirect('sla')->with('fails','SLA Plan can not Update');	
			}
		}
		catch(Exception $e)
		{
			return redirect('sla')->with('fails','SLA Plan can not Update');
		}
	}
	public function destroy($id, Sla_plan $sla)
	{
		try
		{
			$slas = $sla->whereId($id)->first();
			if($slas->delete()==true)
			{
				return redirect('sla')->with('success','SLA Plan Deleted Successfully');
			}
			else
			{
				return redirect('sla')->with('fails','SLA Plan can not Delete');	
			}
		}
		catch(Exception $e)
		{
			return redirect('sla')->with('fails','SLA Plan can not Delete');
		}
	}
}
