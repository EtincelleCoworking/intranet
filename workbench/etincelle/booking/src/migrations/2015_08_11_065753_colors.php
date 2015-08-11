<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ColorsMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ressources', function(Blueprint $table)
		{
			$table->string('booking_background_color', 20);
			$table->string('booking_text_color', 20)->default('#000000');

				// $colors = array('#3a87ad', '#AF8AE5', '#FAD677', '#D06B64');
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
