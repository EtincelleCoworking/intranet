<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationAddress extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('locations', function (Blueprint $table) {
            $table->string('address');
            $table->string('zipcode');
            $table->string('access_url');
        });
        Schema::table('ressources', function (Blueprint $table) {
            $table->integer('subscription_id')->unsigned()->nullable();
            $table->foreign('subscription_id')
                ->references('id')->on('subscription')
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
