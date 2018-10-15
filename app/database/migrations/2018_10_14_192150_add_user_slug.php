<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserSlug extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('slug');
            $table->unique('slug');

        });
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        DB::update('UPDATE locations set is_active = FALSE WHERE city_id = (SELECT id FROM cities WHERE name ="Montauban")');

        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->index('slug');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->integer('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('jobs')->onDelete('CASCADE');
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
