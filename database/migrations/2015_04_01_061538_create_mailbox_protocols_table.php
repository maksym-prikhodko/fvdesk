<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateMailboxProtocolsTable extends Migration {
	public function up()
	{
		Schema::create('mailbox_protocols', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::drop('mailbox_protocols');
	}
}
