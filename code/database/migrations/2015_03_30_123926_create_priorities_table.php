<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreatePrioritiesTable extends Migration {
	public function up()
	{
		Schema::create('priorities', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::drop('priorities');
	}
}
