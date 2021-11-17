<?php
Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
$router->get('getmail/{token}', 'Auth\AuthController@getMail');
Route::group(['middleware' => 'roles', 'roles' => 'user'], function () {
	Route::resource('groups', 'Admin\GroupController'); 
	Route::resource('departments', 'Admin\DepartmentController'); 
	Route::resource('teams', 'Admin\TeamController'); 
	Route::resource('agents', 'Admin\AgentController'); 
	Route::resource('emails', 'Admin\EmailsController'); 
	Route::resource('banlist', 'Admin\BanlistController'); 
	Route::resource('template', 'Admin\TemplateController'); 
	Route::get('getdiagno', 'Admin\TemplateController@formDiagno'); 
	Route::post('postdiagno', 'Admin\TemplateController@postDiagno'); 
	Route::resource('helptopic', 'Admin\HelptopicController'); 
	Route::resource('sla', 'Admin\SlaController'); 
	Route::resource('form', 'Admin\FormController'); 
	Route::get('getcompany', 'Admin\SettingsController@getcompany'); 
	Route::patch('postcompany/{id}', 'Admin\SettingsController@postcompany'); 
	Route::get('getsystem', 'Admin\SettingsController@getsystem'); 
	Route::patch('postsystem/{id}', 'Admin\SettingsController@postsystem'); 
	Route::get('getticket', 'Admin\SettingsController@getticket'); 
	Route::patch('postticket/{id}', 'Admin\SettingsController@postticket'); 
	Route::get('getemail', 'Admin\SettingsController@getemail'); 
	Route::patch('postemail/{id}', 'Admin\SettingsController@postemail'); 
	Route::get('getaccess', 'Admin\SettingsController@getaccess'); 
	Route::patch('postaccess/{id}', 'Admin\SettingsController@postaccess'); 
	Route::get('getresponder', 'Admin\SettingsController@getresponder'); 
	Route::patch('postresponder/{id}', 'Admin\SettingsController@postresponder'); 
	Route::get('getalert', 'Admin\SettingsController@getalert'); 
	Route::patch('postalert/{id}', 'Admin\SettingsController@postalert'); 
	Route::get('admin-profile', 'Admin\ProfileController@getProfile');
	Route::patch('admin-profile', 'Admin\ProfileController@postProfile');
	Route::patch('admin-profile-password', 'Admin\ProfileController@postProfilePassword');
});
Route::get('time', function () {
	return view('themes.default1.admin.tickets.timeline');
});
Route::group(['middleware' => 'role.agent'], function () {
	Route::resource('user', 'Agent\UserController');
	Route::resource('organizations', 'Agent\OrganizationController');
	Route::get('agent-profile', 'Agent\UserController@getProfile');
	Route::patch('agent-profile', 'Agent\UserController@postProfile');
	Route::patch('agent-profile-password', 'Agent\UserController@postProfilePassword');
	Route::get('/test', ['as' => 'thr', 'uses' => 'Agent\MailController@fetchdata']);
	Route::get('/ticket', ['as' => 'ticket', 'uses' => 'Agent\TicketController@ticket_list']);
	Route::get('/ticket/open', ['as' => 'open.ticket', 'uses' => 'Agent\TicketController@open_ticket_list']);
	Route::get('/ticket/answered', ['as' => 'answered.ticket', 'uses' => 'Agent\TicketController@answered_ticket_list']);
	Route::get('/ticket/myticket', ['as' => 'myticket.ticket', 'uses' => 'Agent\TicketController@myticket_ticket_list']);
	Route::get('/ticket/overdue', ['as' => 'overdue.ticket', 'uses' => 'Agent\TicketController@overdue_ticket_list']);
	Route::get('/ticket/closed', ['as' => 'closed.ticket', 'uses' => 'Agent\TicketController@closed_ticket_list']);
	Route::get('/newticket', ['as' => 'newticket', 'uses' => 'Agent\TicketController@newticket']);
	Route::post('/newticket/post', ['as' => 'post.newticket', 'uses' => 'Agent\TicketController@post_newticket']);
	Route::get('/thread/{id}', ['as' => 'ticket.thread', 'uses' => 'Agent\TicketController@thread']);
	Route::patch('/thread/reply/{id}', ['as' => 'ticket.reply', 'uses' => 'Agent\TicketController@reply']);
	Route::patch('/internal/note/{id}', ['as' => 'Internal.note', 'uses' => 'Agent\TicketController@InternalNote']);
	Route::patch('/ticket/assign/{id}', ['as' => 'assign.ticket', 'uses' => 'Agent\TicketController@assign']);
	Route::patch('/ticket/post/edit/{id}', ['as' => 'ticket.post.edit', 'uses' => 'Agent\TicketController@ticket_edit_post']);
	Route::get('/ticket/print/{id}', ['as' => 'ticket.print', 'uses' => 'Agent\TicketController@ticket_print']);
	Route::get('/ticket/close/{id}', ['as' => 'ticket.close', 'uses' => 'Agent\TicketController@close']);
	Route::get('/ticket/resolve/{id}', ['as' => 'ticket.resolve', 'uses' => 'Agent\TicketController@resolve']);
	Route::get('/ticket/open/{id}', ['as' => 'ticket.open', 'uses' => 'Agent\TicketController@open']);
	Route::get('/ticket/delete/{id}', ['as' => 'ticket.delete', 'uses' => 'Agent\TicketController@delete']);
	Route::get('/email/ban/{id}', ['as' => 'ban.email', 'uses' => 'Agent\TicketController@ban']);
	Route::get('/ticket/surrender/{id}', ['as' => 'ticket.surrender', 'uses' => 'Agent\TicketController@surrender']);
	Route::get('/aaaa', 'Guest\GuestController@ticket_number');
});
$router->get('getform', 'Guest\FormController@getForm');
$router->post('postform', 'Guest\FormController@postForm');
$router->post('postedform', 'Guest\FormController@postedForm');
$router->get('check', 'CheckController@getcheck');
$router->post('postcheck/{id}', 'CheckController@postcheck');
$router->get('guest', 'Guest\OuthouseController@get');
Route::group(['middleware' => 'role.user', 'roles' => 'user'], function () {
	Route::get('user-profile', 'Guest\GuestController@getProfile');
	Route::patch('profile', 'Guest\GuestController@postProfile');
	Route::patch('profile-password', 'Guest\GuestController@postProfilePassword');
});
$router->get('myticket', ['as' => 'ticket', 'uses' => 'Guest\GuestController@getMyticket']);
$router->get('checkticket', 'Guest\GuestController@getCheckTicket');
$router->post('postcheck', 'Guest\GuestController@PostCheckTicket');
$router->get('postcheck', 'Guest\GuestController@PostCheckTicket');
$router->get('404', 'error\ErrorController@error404');
