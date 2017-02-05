<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountPropertiesMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('cashflow_account', function (Blueprint $table) {
            $table->string('account_number')->nullable();
            $table->date('amount_updated_at')->nullable();
        });
        DB::connection()->getPdo()->exec('UPDATE cashflow_account SET amount_updated_at = updated_at');

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
