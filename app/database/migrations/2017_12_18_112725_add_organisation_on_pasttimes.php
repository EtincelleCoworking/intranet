<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrganisationOnPasttimes extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('past_times', function (Blueprint $table) {
            $table->integer('organisation_id')->unsigned()->nullable();
            $table->foreign('organisation_id')
                ->references('id')->on('organisations')
                ->onDelete('CASCADE');
        });

        DB::connection()->getPdo()->exec('update past_times set organisation_id = (SELECT organisation_id FROM invoices WHERE id = past_times.invoice_id)');

        /*
          update past_times set organisation_id = (SELECT distinct(booking.organisation_id) FROM booking JOIN booking_item ON booking.id = booking_item.booking_id

        WHERE past_times.user_id = booking.user_id
    AND past_times.ressource_id = booking_item.ressource_id
    AND past_times.date_past = DATE_FORMAT(booking_item.start_at, "%Y-%m-%d")
    AND past_times.time_start = booking_item.start_at
    AND past_times.time_end = booking_item.start_at + INTERVAL booking_item.duration MINUTE
    AND past_times.organisation_id IS NULL
    )
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
