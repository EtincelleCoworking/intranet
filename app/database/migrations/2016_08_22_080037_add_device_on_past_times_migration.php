<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeviceOnPastTimesMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('past_times', function (Blueprint $table) {
            $table->integer('device_id')->unsigned()->nullable();
            $table->foreign('device_id')
                ->references('id')->on('devices')
                ->onDelete('CASCADE');
            $table->dropColumn('auto_updated');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->string('slack_endpoint')->nullable();
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
