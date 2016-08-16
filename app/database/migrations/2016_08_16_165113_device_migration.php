<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeviceMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('devices',
            function (Blueprint $table) {
                $table->increments('id');

                $table->string('mac', 20);
                $table->unique('mac');

                $table->timestamps();

                $table->integer('user_id')->unsigned()->nullable();
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('CASCADE');

                $table->string('name', 255);
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
