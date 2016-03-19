<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LocationIpMigration extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->timestamps();
                $table->unique('name');
            }
        );

        Schema::create('locations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->timestamps();
                $table->unique('name');
                $table->integer('city_id')->unsigned()->nullable();
                $table->foreign('city_id')
                    ->references('id')->on('cities')
                    ->onDelete('CASCADE');
            }
        );

        Schema::create('locations_ips',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 15);
                $table->timestamps();
                $table->unique(array('id', 'name'));
                $table->foreign('id')
                    ->references('id')->on('locations')
                    ->onDelete('CASCADE');

            }
        );
        Schema::table('users', function (Blueprint $table) {
            $table->integer('default_location_id')->unsigned()->nullable();
            $table->foreign('default_location_id')
                ->references('id')->on('locations')
                ->onDelete('SET NULL');
        });
        Schema::table('ressources', function (Blueprint $table) {
            $table->integer('location_id')->unsigned()->nullable();
            $table->foreign('location_id')
                ->references('id')->on('locations')
                ->onDelete('SET NULL');
        });

        $locations = array();
        $locations['Toulouse'] = array('Wilson', 'Carmes');
        $locations['Montauban'] = array('');
        foreach ($locations as $cityName => $names) {
            $city = new City();
            $city->name = $cityName;
            $city->save();
            foreach ($names as $name) {
                $location = new Location();
                $location->city_id = $city->id;
                $location->name = $name;
                $location->save();
            }
        }
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
