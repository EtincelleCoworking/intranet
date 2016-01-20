<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InvoiceReminder extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('invoices', function (Blueprint $table) {
			$table->dateTime('sent_at')->nullable();
			$table->dateTime('reminder1_at')->nullable();
			$table->dateTime('reminder2_at')->nullable();
			$table->dateTime('reminder3_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('invoices', function (Blueprint $table) {
			$table->dropColumn('sent_at');
			$table->dropColumn('reminder1_at');
			$table->dropColumn('reminder2_at');
			$table->dropColumn('reminder3_at');
		});
	}

}
