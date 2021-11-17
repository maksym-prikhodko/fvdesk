<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateGuestNotesTable extends Migration {
	public function up()
	{
		Schema::create('guest_notes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::drop('guest_notes');
	}
}
