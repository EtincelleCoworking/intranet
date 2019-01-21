<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoworkingPrepaid extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
/*
        Schema::create('coworking_prepaid_pack',
            function (Blueprint $table) {
                $table->integer('invoice_item_id')->unsigned();
                $table->unique('invoice_item_id');
                $table->foreign('invoice_item_id')
                    ->references('id')->on('invoices_items')
                    ->onDelete('CASCADE');
            }
        );
*/

/*
        Schema::create('coworking_prepaid_pack_item',
            function (Blueprint $table) {
                //$table->increments('id');
                $table->integer('order_index')->unsigned();
                $table->integer('coworking_prepaid_pack_id')->unsigned();
                $table->foreign('coworking_prepaid_pack_id')
                    ->references('id')->on('coworking_prepaid_pack')
                    ->onDelete('CASCADE');
                $table->unique(array('order_index', 'coworking_prepaid_pack_id'));


                $table->integer('invoice_item_id')->unsigned();
                $table->unique('invoice_item_id');
                $table->foreign('invoice_item_id')
                    ->references('id')->on('invoices_items')
                    ->onDelete('CASCADE');

                $table->unique(array('invoice_item_id', 'order_index'));

                $table->integer('past_time_id')->unsigned()->nullable();
                $table->foreign('past_time_id')
                    ->references('id')->on('past_times')
                    ->onDelete('CASCADE');
            }
        );

        Schema::table('invoices_items', function (Blueprint $table) {
            $table->integer('coworking_pack_item_count')->unsigned();

            $table->integer('coworking_pack_item_user_id')->unsigned()->nullable();
            $table->foreign('coworking_pack_item_user_id')
                ->references('id')->on('users')
                ->onDelete('CASCADE');
        });

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
