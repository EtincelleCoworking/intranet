<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalesBooking extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_item', function (Blueprint $table) {
            $table->decimal('sold_price')->nullable();
            $table->dropForeign('booking_item_invoice_id_foreign');
            $table->dropColumn('invoice_id');
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
