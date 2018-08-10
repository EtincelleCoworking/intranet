<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoicingRules extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('invoicing_rules',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->string('kind');
                $table->integer('order_index')->unsigned()->default(0);
                $table->integer('organisation_id')->unsigned();
                $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('CASCADE');
            }
        );

        /*
         INSERT INTO `etincelle_intranet`.`invoicing_rules` (`id`, `kind`, `order_index`, `organisation_id`) VALUES (NULL, 'InvoicingRuleProcessor_MeetingRoomDiscount10', 1, '617');
         INSERT INTO `etincelle_intranet`.`invoicing_rules` (`id`, `kind`, `order_index`, `organisation_id`) VALUES (NULL, 'InvoicingRuleProcessor_MeetingRoomAddDailyCatering_Discount25', 2, '617');
         */
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
