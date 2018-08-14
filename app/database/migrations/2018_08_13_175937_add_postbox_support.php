<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostboxSupport extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('postbox_kind',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->text('name');
                $table->timestamps();
            }
        );
        Schema::create('postbox_notification',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->date('occurs_at');

                $table->integer('organisation_id')->unsigned();
                $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('CASCADE');

                $table->integer('reporter_id')->unsigned()->nullable();
                $table->foreign('reporter_id')->references('id')->on('users')->onDelete('SET NULL');

                $table->dateTime('seen_at')->nullable();
                $table->timestamps();
            }
        );
        Schema::create('postbox_item',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('quantity');
                $table->text('from_name');
                $table->longText('details');
                $table->boolean('is_important')->default(false);
                $table->boolean('is_template')->default(false);

                $table->integer('kind_id')->unsigned()->nullable();
                $table->foreign('kind_id')->references('id')->on('postbox_kind')->onDelete('SET NULL');

                $table->integer('postbox_notification_id')->unsigned();
                $table->foreign('postbox_notification_id')->references('id')->on('postbox_notification')->onDelete('CASCADE');

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
