<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookingAddConfirmationMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('booking_item', function (Blueprint $table) {
            $table->dateTime('confirmed_at')->nullable();
            $table->integer('confirmed_by_user_id')->unsigned()->nullable();
            $table->foreign('confirmed_by_user_id')
                ->references('id')->on('users')
                ->onDelete('SET NULL');
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
