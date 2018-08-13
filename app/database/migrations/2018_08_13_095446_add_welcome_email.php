<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWelcomeEmail extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('locations', function (Blueprint $table) {
            $table->longText('welcome_email_content')->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('welcome_email_sent_at')->nullable();
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
