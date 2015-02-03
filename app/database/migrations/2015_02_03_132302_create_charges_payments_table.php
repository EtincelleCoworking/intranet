<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargesPaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('charges_payments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();

            $table->integer('charge_id')->unsigned();
            $table->date('date_payment');
            $table->string('mode');
            $table->string('description');
            $table->decimal('amount', 8, 2);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('charges_payments');
	}

}
