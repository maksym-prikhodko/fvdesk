<?php namespace App\Model\Email;
use Illuminate\Database\Eloquent\Model;
class Banlist extends Model {
	protected $table = 'banlist';
	protected $fillable = [
		'id', 'ban_status', 'email_address', 'internal_notes',
	];
}
