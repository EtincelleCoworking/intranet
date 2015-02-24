<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoices_comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();

			$table->integer('invoice_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->text('content');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invoices_comments');
	}

}
