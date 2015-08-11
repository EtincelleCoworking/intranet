<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookingMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ressources', function(Blueprint $table)
		{
			$table->boolean('is_bookable')->default(false);
		});
		Schema::create('booking', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->string('title');
			$table->integer('status')->unsigned()->default(0);
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
			$table->boolean('is_private')->default(false);
		});
		Schema::create('booking_item', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->integer('booking_id')->unsigned();
			$table->foreign('booking_id')->references('id')->on('booking');
			$table->dateTime('start_at');
			$table->integer('duration')->unsigned();

			$table->integer('ressource_id')->unsigned();
			$table->foreign('ressource_id')->references('id')->on('ressources');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('booking_item');
		Schema::drop('booking');
	}

}
