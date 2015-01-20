<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompetencesToUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
            $table->string('competence1_title')->nullable();
            $table->integer('competence1_value')->nullable();
            $table->string('competence2_title')->nullable();
            $table->integer('competence2_value')->nullable();
            $table->string('competence3_title')->nullable();
            $table->integer('competence3_value')->nullable();
            $table->string('competence4_title')->nullable();
			$table->integer('competence4_value')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			//
		});
	}

}
