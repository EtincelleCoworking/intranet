<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('organisation_user', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('organisation_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->unique(array('organisation_id', 'user_id'));

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('organisation_user');
	}

}
