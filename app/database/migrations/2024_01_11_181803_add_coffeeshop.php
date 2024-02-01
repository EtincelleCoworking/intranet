<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoffeeshop extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coffeeshop_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('occurs_at');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('CASCADE');
            $table->string('product_slug');
            $table->integer('quantity')->unsigned();
            $table->integer('invoice_id')->unsigned()->nullable();
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('CASCADE');
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
        //
    }

}