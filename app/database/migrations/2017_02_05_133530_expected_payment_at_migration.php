<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExpectedPaymentAtMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('expected_payment_at')->nullable();
        });
        DB::connection()->getPdo()->exec('UPDATE invoices SET expected_payment_at = deadline');

    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
