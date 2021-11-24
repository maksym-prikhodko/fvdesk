<?php namespace App\Model\Agent_panel;
use Illuminate\Database\Eloquent\Model;
class Organization extends Model {
	protected $table = 'organization';
	protected $fillable = ['id','name','phone','website','address','internal_notes'];
}
