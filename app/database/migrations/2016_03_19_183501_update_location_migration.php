<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class UpdateLocationMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('locations', function (Blueprint $table) {
            $table->string('slug', 255)->nullable();
            $table->unique('slug');
		});

		foreach(Location::all() as $location){
			$location->slug = Str::Slug((string)$location);
			$location->save();
		}
		//
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
