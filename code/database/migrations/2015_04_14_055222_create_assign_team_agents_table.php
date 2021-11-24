<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateAssignTeamAgentsTable extends Migration {
	public function up()
	{
		Schema::create('assign_team_agents', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::drop('assign_team_agents');
	}
}
