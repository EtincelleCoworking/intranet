<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneboxSessions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('phonebox',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('order_index')->unsigned();
                $table->integer('location_id')->unsigned();
                $table->foreign('location_id')
                    ->references('id')->on('locations')
                    ->onDelete('CASCADE');
                $table->integer('active_session_id')->unsigned()->nullable();
            }
        );

        Schema::create('phonebox_session',
            function (Blueprint $table) {
                $table->increments('id');

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

        Schema::table('phonebox', function (Blueprint $table) {
            $table->foreign('active_session_id')
                ->references('id')->on('phonebox_session')
                //    ->onDelete('CASCADE')
            ;
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
