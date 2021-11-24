<?php namespace App\Model\Utility;
use Illuminate\Database\Eloquent\Model;
class Email extends Model {
	protected $table = 'email';
	protected $fillable = [
			'id','template','sys_email','alert_email','admin_email','mta','email_fetching','strip',
			'separator','all_emails','email_collaborator','attachment'
		];
}
