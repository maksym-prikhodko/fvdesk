<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateGroupAssignDepartmentsTable extends Migration {
	public function up()
	{
		Schema::create('group_assign_departments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::drop('group_assign_departments');
	}
}
