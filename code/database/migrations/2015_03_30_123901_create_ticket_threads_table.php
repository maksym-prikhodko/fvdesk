<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateTicketThreadsTable extends Migration {
	public function up()
	{
		Schema::create('ticket_threads', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::drop('ticket_threads');
	}
}
