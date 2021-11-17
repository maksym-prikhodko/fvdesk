<?php namespace App\Model\Agent_panel;
use Illuminate\Database\Eloquent\Model;
class Sys_user extends Model {
	protected $table = 'sys_user';
	protected $fillable = ['id','email','full_name','phone','internal_notes'];
}
