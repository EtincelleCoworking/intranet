<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserTags extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('slug');
            $table->unique('slug');
            $table->boolean('is_highlighted')->default(false);
        });

        Schema::create('user_hashtag',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('CASCADE');

                $table->integer('hashtag_id')->unsigned();
                $table->foreign('hashtag_id')
                    ->references('id')->on('hashtags')
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
