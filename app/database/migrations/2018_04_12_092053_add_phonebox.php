<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhonebox extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
/*
        Schema::create('phonebox',
            function (Blueprint $table) {
                $table->string('name');
                $table->integer('local_id')->unsigned();
                $table->integer('location_id')->unsigned();
                $table->foreign('location_id')
                    ->references('id')->on('locations')
                    ->onDelete('CASCADE');
            }
        );

        Schema::create('phonebox_session',
            function (Blueprint $table) {
                $table->integer('phonebox_id')->unsigned();
                $table->foreign('phonebox_id')
                    ->references('id')->on('phonebox')
                    ->onDelete('CASCADE');

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('CASCADE');

                $table->dateTime('started_at');
                $table->dateTime('ended_at')->nullable();
                $table->boolean('ended_auto')->default(true);
            }
        );
*/
        Schema::table('users', function (Blueprint $table) {
            $table->string('personnal_code', 6)->nullable();
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
