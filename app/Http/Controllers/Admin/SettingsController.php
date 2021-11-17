<?php namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\EmailRequest;
use App\Http\Requests\SystemRequest;
use App\Model\Agent\Department;
use App\Model\Email\Emails;
use App\Model\Email\Template;
use App\Model\Manage\Help_topic;
use App\Model\Manage\Sla_plan;
use App\Model\Settings\Access;
use App\Model\Settings\Alert;
use App\Model\Settings\Company;
use App\Model\Settings\Email;
use App\Model\Settings\Responder;
use App\Model\Settings\System;
use App\Model\Settings\Ticket;
use App\Model\Utility\Date_format;
use App\Model\Utility\Date_time_format;
use App\Model\Utility\Logs;
use App\Model\Utility\Priority;
use App\Model\Utility\Timezones;
use App\Model\Utility\Time_format;
use Illuminate\Http\Request;
use Input;
class SettingsController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function getcompany(Company $company) {
		try
		{
			$companys = $company->whereId('1')->first();
			return view('themes.default1.admin.settings.company', compact('companys'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function postcompany($id, Company $company, CompanyRequest $request) {
		try
		{
			$companys = $company->whereId('1')->first();
			if (Input::file('logo')) {
				$name = Input::file('logo')->getClientOriginalName();
				$destinationPath = 'dist';
				$fileName = rand(0000, 9999) . '.' . $name;
				Input::file('logo')->move($destinationPath, $fileName);
				$companys->logo = $fileName;
			}
			if ($companys->fill($request->except('logo'))->save() == true) {
				return redirect('getcompany')->with('success', 'Company Updated Successfully');
			} else {
				return redirect('getcompany')->with('fails', 'Company can not Updated');
			}
		} catch (Exception $e) {
			return redirect('getcompany')->with('fails', 'Company can not Updated');
		}
	}
	public function getsystem(System $system, Department $department, Timezones $timezone, Date_format $date, Date_time_format $date_time, Time_format $time, Logs $log) {
		try
		{
			$systems = $system->whereId('1')->first();
			$departments = $department->get();
			$timezones = $timezone->get();
			return view('themes.default1.admin.settings.system', compact('systems', 'departments', 'timezones', 'time', 'date', 'date_time', 'log'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function postsystem($id, System $system, SystemRequest $request) {
		try
		{
			$systems = $system->whereId('1')->first();
			if ($systems->fill($request->input())->save() == true) {
				return redirect('getsystem')->with('success', 'System Updated Successfully');
			} else {
				return redirect('getsystem')->with('fails', 'System can not Updated');
			}
		} catch (Exception $e) {
			return redirect('getsystem')->with('fails', 'System can not Updated');
		}
	}
	public function getticket(Ticket $ticket, Sla_plan $sla, Help_topic $topic, Priority $priority) {
		try
		{
			$tickets = $ticket->whereId('1')->first();
			$slas = $sla->get();
			$topics = $topic->get();
			return view('themes.default1.admin.settings.ticket', compact('tickets', 'slas', 'topics', 'priority'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function postticket($id, Ticket $ticket, Request $request) {
		try
		{
			$tickets = $ticket->whereId('1')->first();
			$tickets->fill($request->except('captcha', 'claim_response', 'assigned_ticket', 'answered_ticket', 'agent_mask', 'html', 'client_update'))->save();
			$tickets->captcha = $request->input('captcha');
			$tickets->claim_response = $request->input('claim_response');
			$tickets->assigned_ticket = $request->input('assigned_ticket');
			$tickets->answered_ticket = $request->input('answered_ticket');
			$tickets->agent_mask = $request->input('agent_mask');
			$tickets->html = $request->input('html');
			$tickets->client_update = $request->input('client_update');
			if ($tickets->save() == true) {
				return redirect('getticket')->with('success', 'Ticket Updated Successfully');
			} else {
				return redirect('getticket')->with('fails', 'Ticket can not Updated');
			}
		} catch (Exception $e) {
			return redirect('getticket')->with('fails', 'Ticket can not Updated');
		}
	}
	public function getemail(Email $email, Template $template, Emails $email1) {
		try
		{
			$emails = $email->whereId('1')->first();
			$templates = $template->get();
			$emails1 = $email1->get();
			return view('themes.default1.admin.settings.email', compact('emails', 'templates', 'emails1'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function postemail($id, Email $email, EmailRequest $request) {
		try
		{
			$emails = $email->whereId('1')->first();
			$emails->fill($request->except('email_fetching', 'all_emails', 'email_collaborator', 'strip', 'attachment'))->save();
			$emails->email_fetching = $request->input('email_fetching');
			$emails->all_emails = $request->input('all_emails');
			$emails->email_collaborator = $request->input('email_collaborator');
			$emails->strip = $request->input('strip');
			$emails->attachment = $request->input('attachment');
			if ($emails->save() == true) {
				return redirect('getemail')->with('success', 'Email Updated Successfully');
			} else {
				return redirect('getemail')->with('fails', 'Email can not Updated');
			}
		} catch (Exception $e) {
			return redirect('getemail')->with('fails', 'Email can not Updated');
		}
	}
	public function getaccess(Access $access) {
		try
		{
			$accesses = $access->whereId('1')->first();
			return view('themes.default1.admin.settings.access', compact('accesses'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function postaccess(Access $access, Request $request) {
		try
		{
			$accesses = $access->whereId('1')->first();
			$accesses->fill($request->except('password_reset', 'bind_agent_ip', 'reg_require', 'quick_access'))->save();
			$accesses->password_reset = $request->input('password_reset');
			$accesses->bind_agent_ip = $request->input('bind_agent_ip');
			$accesses->reg_require = $request->input('reg_require');
			$accesses->quick_access = $request->input('quick_access');
			if ($accesses->save() == true) {
				return redirect('getaccess')->with('success', 'Access Updated Successfully');
			} else {
				return redirect('getaccess')->with('fails', 'Access can not Updated');
			}
		} catch (Exception $e) {
			return redirect('getaccess')->with('fails', 'Access can not Updated');
		}
	}
	public function getresponder(Responder $responder) {
		try
		{
			$responders = $responder->whereId('1')->first();
			return view('themes.default1.admin.settings.responder', compact('responders'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function postresponder(Responder $responder, Request $request) {
		try
		{
			$responders = $responder->whereId('1')->first();
			$responders->new_ticket = $request->input('new_ticket');
			$responders->agent_new_ticket = $request->input('agent_new_ticket');
			$responders->submitter = $request->input('submitter');
			$responders->partcipants = $request->input('partcipants');
			$responders->overlimit = $request->input('overlimit');
			if ($responders->save() == true) {
				return redirect('getresponder')->with('success', 'Responder Updated Successfully');
			} else {
				return redirect('getresponder')->with('fails', 'Responder can not Updated');
			}
		} catch (Exception $e) {
			return redirect('getresponder')->with('fails', 'Responder can not Updated');
		}
	}
	public function getalert(Alert $alert) {
		try
		{
			$alerts = $alert->whereId('1')->first();
			return view('themes.default1.admin.settings.alert', compact('alerts'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function postalert($id, Alert $alert, Request $request) {
		try
		{
			$alerts = $alert->whereId('1')->first();
			$alerts->assignment_status = $request->input('assignment_status');
			$alerts->ticket_status = $request->input('ticket_status');
			$alerts->overdue_department_member = $request->input('overdue_department_member');
			$alerts->sql_error = $request->input('sql_error');
			$alerts->excessive_failure = $request->input('excessive_failure');
			$alerts->overdue_status = $request->input('overdue_status');
			$alerts->overdue_assigned_agent = $request->input('overdue_assigned_agent');
			$alerts->overdue_department_manager = $request->input('overdue_department_manager');
			$alerts->internal_status = $request->input('internal_status');
			$alerts->internal_last_responder = $request->input('internal_last_responder');
			$alerts->internal_assigned_agent = $request->input('internal_assigned_agent');
			$alerts->internal_department_manager = $request->input('internal_department_manager');
			$alerts->assignment_assigned_agent = $request->input('assignment_assigned_agent');
			$alerts->assignment_team_leader = $request->input('assignment_team_leader');
			$alerts->assignment_team_member = $request->input('assignment_team_member');
			$alerts->system_error = $request->input('system_error');
			$alerts->transfer_department_member = $request->input('transfer_department_member');
			$alerts->transfer_department_manager = $request->input('transfer_department_manager');
			$alerts->transfer_assigned_agent = $request->input('transfer_assigned_agent');
			$alerts->transfer_status = $request->input('transfer_status');
			$alerts->message_organization_accmanager = $request->input('message_organization_accmanager');
			$alerts->message_department_manager = $request->input('message_department_manager');
			$alerts->message_assigned_agent = $request->input('message_assigned_agent');
			$alerts->message_last_responder = $request->input('message_last_responder');
			$alerts->message_status = $request->input('message_status');
			$alerts->ticket_organization_accmanager = $request->input('ticket_organization_accmanager');
			$alerts->ticket_department_manager = $request->input('ticket_department_manager');
			$alerts->ticket_department_member = $request->input('ticket_department_member');
			$alerts->ticket_admin_email = $request->input('ticket_admin_email');
			if ($alerts->save() == true) {
				return redirect('getalert')->with('success', 'Alert Updated Successfully');
			} else {
				return redirect('getalert')->with('fails', 'Alert can not Updated');
			}
		} catch (Exception $e) {
			return redirect('getalert')->with('fails', 'Alert can not Updated');
		}
	}
	public function getck() {
		return view('themes.default1.ckeditor');
	}
}
