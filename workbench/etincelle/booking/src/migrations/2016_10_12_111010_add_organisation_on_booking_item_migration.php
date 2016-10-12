<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrganisationOnBookingItemMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('booking', function (Blueprint $table) {
            $table->integer('organisation_id')->unsigned()->nullable();
            $table->foreign('organisation_id')
                ->references('id')->on('organisations')
                ->onDelete('CASCADE');
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
