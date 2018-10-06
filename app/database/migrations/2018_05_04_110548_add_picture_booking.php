<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPictureBooking extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        Schema::table('booking_item', function (Blueprint $table) {
            $table->text('internal_notes');
            $table->integer('participant_count')->unsigned()->nullable();
        });

        Schema::table('ressources', function (Blueprint $table) {
            $table->string('picture')->nullable();
            $table->decimal('floor_space');
        });

        Schema::create('booking_item_layout',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->text('description');
                $table->string('picture1')->nullable();
                $table->string('picture2')->nullable();
                $table->string('picture3')->nullable();
                $table->string('picture4')->nullable();
                $table->integer('max_participants')->unsigned()->nullable();
                $table->integer('ressource_id')->unsigned()->nullable();
                $table->foreign('ressource_id')
                    ->references('id')->on('ressources')
                    ->onDelete('CASCADE');
                $table->timestamps();
            }
        );

        Schema::create('booking_item_option_type',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->text('description');
                $table->decimal('amount_fix');
                $table->decimal('amount_participant');
                $table->timestamps();
            }
        );

        Schema::create('booking_item_option',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('quantity')->unsigned()->nullable();
                $table->integer('booking_item_option_type_id')->unsigned()->nullable();
                $table->foreign('booking_item_option_type_id')
                    ->references('id')->on('booking_item_option_type')
                    ->onDelete('CASCADE');
                $table->integer('booking_item_id')->unsigned()->nullable();
                $table->foreign('booking_item_id')
                    ->references('id')->on('booking_item')
                    ->onDelete('CASCADE');
                $table->timestamps();
            }
        );

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
