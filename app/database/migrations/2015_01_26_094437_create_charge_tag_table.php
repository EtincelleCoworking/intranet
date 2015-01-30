<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargeTagTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('charge_tag', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('charge_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->unique(array('charge_id', 'tag_id'));
            $table->foreign('charge_id')->references('id')->on('charges');
            $table->foreign('tag_id')->references('id')->on('tags');
            $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('charge_tag');
	}

}
