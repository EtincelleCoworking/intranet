<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeepboxSupport extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locker_cabinet',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->integer('location_id')->unsigned();
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('CASCADE');

                $table->string('name');
                $table->string('description');
            }
        );
        Schema::create('locker',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->integer('locker_cabinet_id')->unsigned();
                $table->foreign('locker_cabinet_id')
                    ->references('id')->on('locker_cabinet')
                    ->onDelete('CASCADE');

                $table->string('name');
                $table->string('secret');
            }
        );

        Schema::create('locker_history',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('CASCADE');

                $table->integer('locker_id')->unsigned();
                $table->foreign('locker_id')
                    ->references('id')->on('locker')
                    ->onDelete('CASCADE');

                $table->dateTime('taken_at');
                $table->dateTime('released_at')->nullable();
            }
        );

        Schema::table('locker', function (Blueprint $table) {
            $table->integer('current_usage_id')->unsigned()->nullable();
            $table->foreign('current_usage_id')
                ->references('id')->on('locker_history')
                ->onDelete('CASCADE');
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
