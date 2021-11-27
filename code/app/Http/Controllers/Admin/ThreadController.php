<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Model\Priority;
use App\Model\Ticket_thread;
class ThreadController extends Controller {
	public function getTickets(Ticket_thread $thread, Priority $priority) {
		try {
			$threads = $thread->get();
			$priorities = $priority->get();
			return view('themes.default1.admin.tickets.ticket', compact('threads', 'priorities'));
		} catch (Exception $e) {
			return view('404');
		}
	}
}
