<?php namespace App;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
class User extends Model implements AuthenticatableContract, CanResetPasswordContract {
	use Authenticatable, CanResetPassword;
	protected $table = 'users';
	protected $fillable = ['user_name', 'email', 'password','first_name','last_name','ext','mobile','profile_pic',
							'phone_number','company','agent_sign','account_type','account_status',
							'assign_group','primary_dpt','agent_tzone','daylight_save','limit_access',
							'directory_listing','vocation_mode','role'];
	protected $hidden = ['password', 'remember_token'];
}
