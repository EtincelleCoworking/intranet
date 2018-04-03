<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImproveQuoteContent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('business_terms')->nullable(true);
        });
        Schema::table('locations', function (Blueprint $table) {
            $table->text('default_business_terms')->nullable(true);
            $table->text('sales_presentation')->nullable(true);
        });
        Schema::table('ressources', function (Blueprint $table) {
            $table->text('sales_presentation')->nullable(true);
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
