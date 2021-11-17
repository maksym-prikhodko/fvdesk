<?php namespace App\Model\Settings;
use Illuminate\Database\Eloquent\Model;
class Ticket extends Model {
	protected $table = 'ticket_settings';
	protected $fillable = [
		'id','num_format','num_sequence','priority','sla','help_topic','max_open_ticket','collision_avoid',
		'captcha','status','claim_response','assigned_ticket','answered_ticket','agent_mask','html','client_update','max_file_size'
	];
}
