<?php
namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\TicketRequest;
use App\Model\Email\Banlist;
use App\Model\Ticket\Tickets;
use App\Model\Ticket\Ticket_Thread;
use App\User;
use Auth;
use Hash;
use Input;
use Mail;
use PDF;
class TicketController extends Controller {
	public function __construct() {
		$this->middleware('auth');
	}
	public function ticket_list() {
		return view('themes.default1.agent.ticket.ticket');
	}
	public function open_ticket_list() {
		return view('themes.default1.agent.ticket.open');
	}
	public function answered_ticket_list() {
		return view('themes.default1.agent.ticket.answered');
	}
	public function myticket_ticket_list() {
		return view('themes.default1.agent.ticket.myticket');
	}
	public function overdue_ticket_list() {
		return view('themes.default1.agent.ticket.overdue');
	}
	public function closed_ticket_list() {
		return view('themes.default1.agent.ticket.closed');
	}
	public function newticket() {
		return view('themes.default1.agent.ticket.new');
	}
	public function post_newticket(CreateTicketRequest $request) {
		$email = $request->input('email');
		$fullname = $request->input('fullname');
		$notice = $request->input('notice');
		$helptopic = $request->input('helptopic');
		$dept = $request->input('dept');
		$sla = $request->input('sla');
		$duedate = $request->input('duedate');
		$assignto = $request->input('assignto');
		$subject = $request->input('subject');
		$body = $request->input('body');
		$priority = $request->input('priority');
		$phone = "";
		$system = "";
		if ($this->create_user($email, $fullname, $subject, $body, $phone, $helptopic, $sla, $priority, $system)) {
			return Redirect('newticket')->with('success', 'success');
		} else {
			return Redirect('newticket')->with('fails', 'fails');
		}
	}
	public function thread($id) {
		$tickets = Tickets::where('id', '=', $id)->first();
		$thread = Ticket_Thread::where('ticket_id', '=', $id)->first();
		return view('themes.default1.agent.ticket.timeline', compact('tickets'), compact('thread'));
	}
	public function reply(Ticket_Thread $thread, TicketRequest $request) {
		$thread->ticket_id = $request->input('ticket_ID');
		$thread->poster = 'support';
		$thread->body = $request->input('ReplyContent');
		$thread->save();
		$ticket_id = $request->input('ticket_ID');
		$tickets = Tickets::where('id', '=', $ticket_id)->first();
		$thread = Ticket_Thread::where('ticket_id', '=', $ticket_id)->first();
		return 1;
	}
	public function ticket_edit_post($ticket_id, Ticket_Thread $thread) {
		$threads = $thread->where('ticket_id', '=', $ticket_id)->first();
		if (Input::get('subject') != null && Input::get('body') != null) {
			$threads->title = Input::get('subject');
			$threads->body = Input::get('body');
			if ($threads->save()) {
				return 1;
			} else {
				return 0;
			}
		}
		return 0;
	}
	public function ticket_print($id) {
		$tickets = Tickets::where('id', '=', $id)->first();
		$thread = Ticket_Thread::where('ticket_id', '=', $id)->first();
		$html = view('themes.default1.agent.ticket.pdf', compact('id', 'tickets', 'thread'))->render();
		return PDF::load($html)->show();
	}
	public function ticket_number($ticket_number) {
		$number = $ticket_number;
		$number = explode('-', $number);
		$number1 = $number[0];
		if ($number1 == 'ZZZZ') {
			$number1 = 'AAAA';
		}
		$number2 = $number[1];
		if ($number2 == '9999') {
			$number2 = '0000';
		}
		$number3 = $number[2];
		if ($number3 == '9999999') {
			$number3 = '0000000';
		}
		$number1++;
		$number2++;
		$number3++;
		$number2 = sprintf('%04s', $number2);
		$number3 = sprintf('%07s', $number3);
		$array = array($number1, $number2, $number3);
		$number = implode('-', $array);
		return $number;
	}
	public function check_email($email) {
		$check = User::where('email', '=', $email)->first();
		if ($check == true) {
			return $check;
		} else {
			return false;
		}
	}
	public function create_user($emailadd, $username, $subject, $body, $phone, $helptopic, $sla, $priority, $system) {
		$email;
		$username;
		$checkemail = $this->check_email($emailadd);
		if ($checkemail == false) {
			$password = $this->generateRandomString();
			$user = new User;
			$user->user_name = $username;
			$user->email = $emailadd;
			$user->password = Hash::make($password);
			if ($user->save()) {
				$user_id = $user->id;
				if (Mail::send('emails.pass', ['password' => $password, 'name' => $username], function ($message) use ($emailadd, $username) {
					$message->to($emailadd, $username)->subject('password');
				})) {
				}
			}
		} else {
			$username = $checkemail->username;
			$user_id = $checkemail->id;
		}
		$ticket_number = $this->check_ticket($user_id, $subject, $body, $helptopic, $sla, $priority);
		if (Mail::send('emails.Ticket_Create', ['name' => $username, 'ticket_number' => $ticket_number], function ($message) use ($emailadd, $username, $ticket_number) {
			$message->to($emailadd, $username)->subject('[~' . $ticket_number . ']');
		})) {
			return true;
		}
	}
	public function default_helptopic() {
		$helptopic = "1";
		return $helptopic;
	}
	public function default_sla() {
		$sla = "1";
		return $sla;
	}
	public function default_priority() {
		$priority = "1";
		return $prioirty;
	}
	public function check_ticket($user_id, $subject, $body, $helptopic, $sla, $priority) {
		$read_ticket_number = substr($subject, 0, 6);
		if ($read_ticket_number == 'Re: [~') {
			$separate = explode("]", $subject);
			$new_subject = substr($separate[0], 6, 20);
			$find_number = Tickets::where('ticket_number', '=', $new_subject)->first();
			$thread_body = explode("---Reply above this line---", $body);
			$body = $thread_body[0];
			if (count($find_number) > 0) {
				$id = $find_number->id;
				$ticket_number = $find_number->ticket_number;
				if (isset($id)) {
					if ($this->ticket_thread($subject, $body, $id, $user_id)) {
						return $ticket_number;
					}
				}
			} else {
				$ticket_number = $this->create_ticket($user_id, $subject, $body, $helptopic, $sla, $priority);
				return $ticket_number;
			}
		} else {
			$ticket_number = $this->create_ticket($user_id, $subject, $body, $helptopic, $sla, $priority);
			return $ticket_number;
		}
	}
	public function create_ticket($user_id, $subject, $body, $helptopic, $sla, $priority) {
		$max_number = Tickets::whereRaw('id = (select max(`id`) from tickets)')->get();
		foreach ($max_number as $number) {
			$ticket_number = $number->ticket_number;
		}
		$ticket = new Tickets;
		$ticket->ticket_number = $this->ticket_number($ticket_number);
		$ticket->user_id = $user_id;
		$ticket->help_topic_id = $helptopic;
		$ticket->sla = $sla;
		$ticket->status = '1';
		$ticket->priority_id = $priority;
		$ticket->save();
		$ticket_number = $ticket->ticket_number;
		$id = $ticket->id;
		if ($this->ticket_thread($subject, $body, $id, $user_id) == true) {
			return $ticket_number;
		}
	}
	public function ticket_thread($subject, $body, $id, $user_id) {
		$thread = new Ticket_Thread;
		$thread->user_id = $user_id;
		$thread->ticket_id = $id;
		$thread->poster = 'client';
		$thread->title = $subject;
		$thread->body = $body;
		if ($thread->save()) {
			return true;
		}
	}
	public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	public function close($id, Tickets $ticket) {
		$ticket_status = $ticket->where('id', '=', $id)->first();
		$ticket_status->status = 3;
		$ticket_status->save();
		return "your ticket" . $ticket_status->ticket_number . " has been closed";
	}
	public function resolve($id, Tickets $ticket) {
		$ticket_status = $ticket->where('id', '=', $id)->first();
		$ticket_status->status = 2;
		$ticket_status->save();
		return "your ticket" . $ticket_status->ticket_number . " has been resolved";
	}
	public function open($id, Tickets $ticket) {
		$ticket_status = $ticket->where('id', '=', $id)->first();
		$ticket_status->status = 1;
		$ticket_status->save();
		return "your ticket" . $ticket_status->ticket_number . " has been opened";
	}
	public function delete($id, Tickets $ticket) {
		$ticket_delete = $ticket->where('id', '=', $id)->first();
		$ticket_delete->is_deleted = 0;
		$ticket_delete->status = 5;
		$ticket_delete->save();
		return "your ticket" . $ticket_delete->ticket_number . " has been delete";
	}
	public function ban($id, Tickets $ticket) {
		$ticket_ban = $ticket->where('id', '=', $id)->first();
		$ban_email = $ticket_ban->user_id;
		$user = User::where('id', '=', $ban_email)->first();
		$user->is_ban = 1;
		$user->save();
		$Email = $user->email;
		$ban = Banlist::where('email_address', '=', $Email)->first();
		if ($ban == null) {
			$banlist = new Banlist;
			$banlist->ban_status = 1;
			$banlist->email_address = $user->email;
			$banlist->save();
		}
		return "the user has been banned";
	}
	public function assign($id) {
		$UserEmail = Input::get('user');
		$user = User::where('email', '=', $UserEmail)->first();
		$user_id = $user->id;
		$ticket = Tickets::where('id', '=', $id)->first();
		$ticket->assigned_to = $user_id;
		$ticket->save();
		return 1;
	}
	public function InternalNote($id) {
		$InternalContent = Input::get('InternalContent');
		$thread = Ticket_Thread::where('ticket_id', '=', $id)->first();
		$NewThread = new Ticket_Thread;
		$NewThread->ticket_id = $thread->ticket_id;
		$NewThread->user_id = Auth::user()->id;
		$NewThread->thread_type = 'M';
		$NewThread->poster = Auth::user()->role;
		$NewThread->title = $thread->title;
		$NewThread->body = $InternalContent;
		$NewThread->save();
		return 1;
	}
	public function surrender($id) {
		$ticket = Tickets::where('id', '=', $id)->first();
		$ticket->assigned_to = 0;
		$ticket->save();
		return 1;
	}
	public function trash() {
		return view('themes.default1.agent.ticket.trash');
	}
	public function unassigned() {
		return view('themes.default1.agent.ticket.unassigned');
	}
	public function myticket() {
		return view('themes.default1.agent.ticket.myticket');
	}
}
