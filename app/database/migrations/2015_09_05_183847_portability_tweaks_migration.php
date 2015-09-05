<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PortabilityTweaksMigration extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ressource_kind', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name', 255);
            $table->unique('name');
            $table->integer('order_index')->unsigned();
        });

        Schema::table('ressources', function (Blueprint $table) {
            $table->integer('ressource_kind_id')->unsigned()->nullable();
            $table->foreign('ressource_kind_id')->references('id')->on('ressource_kind')->onDelete('CASCADE');
        });

        Schema::table('organisations', function (Blueprint $table) {
           $table->boolean('is_founder')->default(false);
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
