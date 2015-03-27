<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSkillsFromUser extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('competence1_title');
			$table->dropColumn('competence1_value');
			$table->dropColumn('competence2_title');
            $table->dropColumn('competence2_value');
            $table->dropColumn('competence3_title');
            $table->dropColumn('competence3_value');
            $table->dropColumn('competence4_title');
            $table->dropColumn('competence4_value');

            $table->dropColumn('twitter');
            $table->dropColumn('website');
            $table->dropColumn('avatar');
            $table->dropColumn('bio_short');
            $table->dropColumn('bio_long');
		});

        Schema::table('users', function(Blueprint $table)
        {
            $table->string('twitter', 255)->nullable()->change();
            $table->string('website', 255)->nullable()->change();
            $table->string('avatar', 255)->nullable()->change();
            $table->text('bio_short')->nullable()->change();
            $table->text('bio_long')->nullable()->change();
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
