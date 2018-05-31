<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamPlanning extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_planning_item',
            function (Blueprint $table) {
                $table->increments('id');
                $table->dateTime('start_at');
                $table->dateTime('end_at');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
                $table->integer('location_id')->unsigned();
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('CASCADE');
                $table->timestamps();
            }
        );

        Schema::table('locations', function (Blueprint $table) {
            $table->string('color')->nullable();
            $table->boolean('is_staffed')->default(false);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_staff')->default(false);
        });

        // UPDATE users SET is_staff = 1 WHERE email in ('aurelie@etincelle-coworking.com', 'contact@coworking-toulouse.com', 'caroline@etincelle-coworking.com');
        // UPDATE locations SET is_staffed = 1 WHERE id in (1, 8);

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
