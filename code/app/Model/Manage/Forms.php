<?php namespace App\Model\Manage;
use Illuminate\Database\Eloquent\Model;
class Forms extends Model {
	protected $table = 'forms';
	protected $fillable = ['id','title','instruction','label','type','visibility','variable','internal_notes'];
}
