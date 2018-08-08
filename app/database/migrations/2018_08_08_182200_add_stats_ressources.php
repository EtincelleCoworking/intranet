<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatsRessources extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats_ressource_usage',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->dateTime('occurs_at');
                $table->boolean('busy')->default(false);
                $table->integer('ressource_id')->unsigned();
                $table->foreign('ressource_id')->references('id')->on('ressources')->onDelete('CASCADE');
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
