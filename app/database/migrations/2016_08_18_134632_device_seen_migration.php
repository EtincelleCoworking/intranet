<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeviceSeenMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('devices_seen',
            function (Blueprint $table) {
                $table->increments('id');

                $table->integer('device_id')->unsigned()->nullable();
                $table->foreign('device_id')
                    ->references('id')->on('devices')
                    ->onDelete('CASCADE');

                $table->dateTime('last_seen_at')->nullable();
            }
        );
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
