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
