<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargesItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('charges_items', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('charge_id')->unsigned();
            $table->string('description');
            $table->decimal('amount', 8, 2);
            $table->integer('vat_types_id')->unsigned();
            $table->foreign('vat_types_id')->references('id')->on('vat_types');
            $table->foreign('charge_id')->references('id')->on('charges');
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
		Schema::drop('charges_items');
	}

}
