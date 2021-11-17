<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateFormVisibilitiesTable extends Migration {
	public function up()
	{
		Schema::create('form_visibilities', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::drop('form_visibilities');
	}
}
