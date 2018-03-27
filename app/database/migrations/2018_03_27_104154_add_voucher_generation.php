<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoucherGeneration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('locations', function (Blueprint $table) {
            $table->string('voucher_endpoint', 255)->nullable(true);
            $table->string('voucher_key', 255)->nullable(true);
            $table->string('voucher_secret', 255)->nullable(true);
        });
        Schema::table('booking', function (Blueprint $table) {
            $table->string('wifi_login', 255)->nullable(true);
            $table->string('wifi_password', 255)->nullable(true);
        });
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
