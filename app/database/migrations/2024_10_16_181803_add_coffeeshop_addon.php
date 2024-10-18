<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCoffeeshopAddon extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coffeeshop_orders', function (Blueprint $table) {
            $table->string('product_addon')->nullable();
            $table->string('product_addon_comment')->nullable();
            $table->decimal('product_addon_cost')->unsigned();
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
