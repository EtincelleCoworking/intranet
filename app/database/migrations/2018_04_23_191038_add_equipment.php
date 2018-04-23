<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEquipment extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('equipment',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('ip');
                $table->string('kind');
                $table->longText('data');
                $table->boolean('is_critical')->default(true);
                $table->integer('location_id')->unsigned();
                $table->foreign('location_id')
                    ->references('id')->on('locations')
                    ->onDelete('CASCADE');
                $table->datetime('last_seen_at')->nullable();
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
