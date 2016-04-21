<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SubscriptionOrganisationsMigration extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices_items', function (Blueprint $table) {
            $table->integer('subscription_user_id')->unsigned()->nullable();
            $table->foreign('subscription_user_id')
                ->references('id')->on('users')
                ->onDelete('SET NULL');
        });
        DB::connection()->getPdo()->exec('UPDATE invoices SET user_id = NULL WHERE user_id = 0');
        DB::connection()->getPdo()->exec('UPDATE invoices_items JOIN invoices ON invoices_items.invoice_id = invoices.id SET invoices_items.subscription_user_id = invoices.user_id');
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
