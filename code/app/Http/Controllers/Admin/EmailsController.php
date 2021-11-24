<?php namespace App\Http\Controllers\Admin;
use App\Http\Requests\EmailsRequest;
use App\Http\Requests\EmailsEditRequest;
use App\Http\Controllers\Controller;
use App\Model\Utility\Priority;
use App\Model\Utility\MailboxProtocol;
use Illuminate\Http\Request;
use App\Model\Email\Emails;
use App\Model\Manage\Help_topic;
use App\Model\Agent\Department;
use Crypt;
class EmailsController extends Controller {
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Emails $emails)
	{
		try
		{
			$emails = $emails->get();
			return view('themes.default1.admin.emails.emails.index', compact('emails'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function create(Department $department, Help_topic $help, Priority $priority, MailboxProtocol $mailbox_protocol)
	{
		try
		{
			$departments = $department->get();
			$helps = $help->get();
			$priority = $priority->get();
			$mailbox_protocols = $mailbox_protocol->get();
			return view('themes.default1.admin.emails.emails.create',compact('mailbox_protocols','priority','departments','helps'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function store(Emails $email, EmailsRequest $request)
	{
		try
		{
			$password = $request->input('password');
			$encrypted = Crypt::encrypt($password);
			$email->password = $encrypted;
			if($email->fill($request->except('password'))->save()==true)
			{
				return redirect('emails')->with('success','Email Created sucessfully');
			}
			else
			{
				return redirect('emails')->with('fails','Email can not Create');
			}
		}
		catch(Exception $e)
		{
			return redirect('emails')->with('fails','Email can not Create');
		}
	}
	public function show($id)
	{
	}
	public function edit($id, Department $department, Help_topic $help, Emails $email, Priority $priority, MailboxProtocol $mailbox_protocol)
	{
		try
		{
			$emails = $email->whereId($id)->first();
			$departments = $department->get();
			$helps = $help->get();
			$priority = $priority->get();
			$mailbox_protocols = $mailbox_protocol->get();
			return view('themes.default1.admin.emails.emails.edit',compact('mailbox_protocols','priority','departments','helps','emails'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function update($id, Emails $email, EmailsEditRequest $request)
	{
		$password = $request->input('password');
		$encrypted = Crypt::encrypt($password);
		try
		{
			$emails = $email->whereId($id)->first();
			$emails->password = $encrypted;
			$emails->fill($request->except('password'))->save();
			return redirect('emails')->with('success','Email Updated sucessfully');
		}
		catch(Exception $e)
		{
			return redirect('emails')->with('fails','Email not updated');	
		}
	}
	public function destroy($id, Emails $email)
	{
		try
		{
			$emails = $email->whereId($id)->first();
			if($emails->delete()==true)
			{
				return redirect('emails')->with('success','Email Deleted sucessfully');
			}
			else
			{
				return redirect('emails')->with('fails','Email can not  Delete ');	
			}
		}
		catch(Exception $e)
		{
			return redirect('emails')->with('fails','Email can not  Delete ');
		}
	}
}
