<?php
//
//use Illuminate\Database\Schema\Blueprint;
//use Illuminate\Database\Migrations\Migration;
//
//class BookingInvoiceMigration extends Migration {
//
//	/**
//	 * Run the migrations.
//	 *
//	 * @return void
//	 */
//	public function up()
//	{
//		Schema::table('booking_item', function(Blueprint $table)
//		{
//			$table->integer('invoice_id')->unsigned()->nullable();
//			$table->foreign('invoice_id')->references('id')->on('invoices');
//
//			$table->boolean('is_free')->default(false);
//		});
//	}
//
//	/**
//	 * Reverse the migrations.
//	 *
//	 * @return void
//	 */
//	public function down()
//	{
//		//
//	}
//
//}
