<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImproveSubscriptionKindMigration extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_kind', function (Blueprint $table) {
            $table->string('duration')->nullable();
            $table->integer('ressource_id')->unsigned()->nullable();
            $table->foreign('ressource_id')
                ->references('id')->on('ressources')
                ->onDelete('CASCADE');
        });
        DB::table('subscription_kind')->update(array(
            'duration' => '1 month',
            'ressource_id' => Ressource::TYPE_COWORKING
        ));
        Schema::table('subscription', function (Blueprint $table) {
            $table->dropColumn('duration');
        });

        $kind = new SubscriptionKind();
        $kind->ressource_id = Ressource::TYPE_DOMICILIATION;
        $kind->name = 'Domiciliation commerciale';
        $kind->hours_quota = -1;
        $kind->price = 35*3;
        $kind->duration = '3 months';
        $kind->order_index = 5;
        $kind->save();


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
