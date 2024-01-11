<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationDoorToken extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coffeeshop_orders', function (Blueprint $table) {
            $table->dateTime('occurs_at');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('CASCADE');
            $table->string('product_slug');
            $table->integer('quantity')->unsigned();
            $table->integer('invoice_id')->unsigned();
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('CASCADE');
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
