<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSponsorshipUser extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('users', function (Blueprint $table) {
            $table->integer('affiliation_duration')->unsigned()->default(6);
            $table->integer('affiliate_user_id')->unsigned()->nullable();
            $table->foreign('affiliate_user_id')
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
