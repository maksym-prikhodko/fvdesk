<?php namespace App\Model\Settings;
use Illuminate\Database\Eloquent\Model;
class Responder extends Model {
	protected $table = 'auto_response';
	protected $fillable = [
					'id','new_ticket','agent_new_ticket','submitter','participants','overlimit'
			];
}
