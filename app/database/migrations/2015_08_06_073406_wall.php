<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Wall extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(
			'wall_posts',
			function (Blueprint $table) {
				$table->increments('id');
				$table->string('path', 255)->nullable();
				$table->integer('parent_id')->unsigned()->nullable();
				$table->integer('level')->default(0);
				$table->text('message');
				$table->integer('user_id')->unsigned();
				$table->timestamps();
				$table->index(array('path', 'parent_id', 'level'));
				$table->foreign('parent_id')->references('id')->on('wall_posts')->onDelete('CASCADE');
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
