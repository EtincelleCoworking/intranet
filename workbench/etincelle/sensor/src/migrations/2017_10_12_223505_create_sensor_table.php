<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSensorTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('location_id')->unsigned()->nullable();
            $table->foreign('location_id')
                ->references('id')->on('locations')
                ->onDelete('CASCADE');
            $table->string('name');
            $table->integer('order_index')->unsigned();
            $table->string('slug', 255)->nullable();
            $table->unique(array('location_id', 'slug'));
            $table->timestamps();
        });
        Schema::create('sensor_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id')->unsigned()->nullable();
            $table->foreign('sensor_id')
                ->references('id')->on('sensors')
                ->onDelete('CASCADE');
            $table->integer('value');
            $table->timestamp('occured_at');
            $table->unique(array('sensor_id', 'occured_at'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sensor_logs');
        Schema::drop('sensors');
    }

}
