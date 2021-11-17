<?php namespace App\Http\Controllers\Admin;
use App\Http\Requests\TemplateRequest;
use App\Http\Requests\DiagnoRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\TemplateUdate;
use Illuminate\Http\Request;
use App\Model\Email\Template;
use App\Model\Utility\Languages;
use App\Model\Email\Emails;
use Mail;
class TemplateController extends Controller {
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Template $template)
	{
		try
		{
			$templates = $template->get();
			return view('themes.default1.admin.emails.template.index',compact('templates'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function create(Languages $language, Template $template )
	{
		try
		{
			$templates = $template->get();
			$languages = $language->get();
			return view('themes.default1.admin.emails.template.create',compact('languages','templates'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function store(Template $template, TemplateRequest $request)
	{
		try
		{	
			if($template->fill($request->input())->save()==true)
			{
				return redirect('template')->with('success','Teams  Created Successfully');
			}
			else
			{
				return redirect('template')->with('fails','Teams  can not Create');	
			}
		}
		catch(Exception $e)
		{
			return redirect('template')->with('fails','Teams  can not Create');
		}
	}
	public function show($id)
	{
	}
	public function edit($id, Template $template, Languages $language)
	{
		try
		{
			$templates = $template->whereId($id)->first();
			$languages = $language->get();
			return view('themes.default1.admin.emails.template.edit',compact('templates','languages'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function update($id, Template $template, TemplateUdate $request)
	{
		try
		{
			$templates = $template->whereId($id)->first();
			if($templates->fill($request->input())->save()==true)
			{
				return redirect('template')->with('success','Teams  Updated Successfully');
			}
			else
			{
				return redirect('template')->with('fails','Teams can not Update');	
			}
		}
		catch(Exception $e)
		{
			return redirect('template')->with('fails','Teams can not Update');
		}
	}
	public function destroy($id, Template $template)
	{
		try
		{
			$templates = $template->whereId($id)->first();
			if($templates->delete()==true)
			{
				return redirect('template')->with('success','Teams  Deleted Successfully');
			}
			else
			{
				return redirect('template')->with('fails','Teams  can not Delete');	
			}
		}
		catch(Exception $e)
		{
			return redirect('template')->with('fails','Teams  can not Delete');
		}
	}
	public function formDiagno(Emails $email)
	{
		try
		{
			$emails = $email->get();
			return view('themes.default1.admin.emails.template.formDiagno', compact('emails'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function postDiagno(Request $request)
	{
		$email = $request->input('to');
		$subject = $request->input('subject');
		$mail =  Mail::send('themes.default1.admin.emails.template.connection',array('link' => url('getmail'), 'username' => $email),  function($message) use($email) {
                        $message->to($email)->subject('Checking the connection');
                    });
		return redirect('getdiagno')->with('success','Activate Your Account ! Click on Link that send to your mail');
	}
}
