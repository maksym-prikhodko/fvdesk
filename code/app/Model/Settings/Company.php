<?php namespace App\Model\Settings;
use Illuminate\Database\Eloquent\Model;
class Company extends Model
{
	protected $table = 'company';
	protected $fillable = 	[	
								'company_name', 'website', 'phone', 'address', 'landing_page', 'offline_page', 
								'thank_page', 'logo'
							];
}
