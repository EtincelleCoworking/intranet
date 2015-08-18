<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewPropertiesBooking extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking', function (Blueprint $table) {
            //$table->boolean('is_private')->default(true);
            $table->text('content')->nullable();
        });
        DB::statement('ALTER TABLE booking CHANGE COLUMN is_private is_private int(1) NOT NULL DEFAULT 1');
        DB::statement('UPDATE booking SET is_private = true');
        Schema::table('booking_item', function (Blueprint $table) {
            $table->boolean('is_open_to_registration')->default(false);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('booking_key', 20)->nullable();
            $table->unique('booking_key');
        });
        DB::statement('UPDATE users SET booking_key = md5(UUID())  WHERE booking_key IS NULL');
        Schema::create('booking_item_user', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('booking_item_id')->unsigned();
            $table->foreign('booking_item_id')->references('id')->on('booking_item')->onDelete('CASCADE');
            $table->integer('users_id')->unsigned();
            $table->foreign('users_id')->references('id')->on('users')->onDelete('CASCADE');
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
