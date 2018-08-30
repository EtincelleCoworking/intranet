<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeviceSeenRange extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('devices_seen_range',
            function (Blueprint $table) {
                $table->increments('id');

                $table->integer('device_id')->unsigned()->nullable();
                $table->foreign('device_id')
                    ->references('id')->on('devices')
                    ->onDelete('CASCADE');

                $table->dateTime('start_at');
                $table->dateTime('end_at');

                $table->integer('location_id')->unsigned()->nullable();
                $table->foreign('location_id')
                    ->references('id')->on('locations')
                    ->onDelete('CASCADE');
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
