<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeDaysNumberToInvoices extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('invoices', function(Blueprint $table)
		{
			$table->char('type', 1);
			$table->integer('days');
			$table->integer('number');
			$table->unique(array('type', 'days', 'number'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('invoices', function(Blueprint $table)
		{
			//
		});
	}

}
