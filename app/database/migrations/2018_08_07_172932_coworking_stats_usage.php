<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoworkingStatsUsage extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('stats_coworking_usage',
            function (Blueprint $table) {
                $table->increments('id');
                $table->dateTime('occurs_at');
                $table->integer('count')->unsigned();
                $table->integer('capacity')->unsigned();
                $table->integer('location_id')->unsigned();
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('CASCADE');
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
