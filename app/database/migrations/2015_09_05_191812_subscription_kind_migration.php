<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SubscriptionKindMigration extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_kind', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name', 255);
            $table->unique('name');
            $table->integer('hours_quota');
            $table->decimal('price');
            $table->integer('order_index')->unsigned();
        });

        Schema::table('subscription', function (Blueprint $table) {
            $table->integer('subscription_kind_id')->unsigned()->nullable();
            $table->foreign('subscription_kind_id')->references('id')->on('subscription_kind')->onDelete('CASCADE');
        });
        $this->createPlan('Coworking - Abonnement 1/4 temps', 40, 60);
        $this->createPlan('Coworking - Abonnement 1/2 temps', 80, 110);
        $this->createPlan('Coworking - Abonnement illimité', -1, 165);
        DB::statement('UPDATE subscription SET subscription_kind_id = (SELECT id FROM subscription_kind WHERE subscription_kind.price = subscription.amount LIMIT 1)');
        Schema::table('subscription', function (Blueprint $table) {
            $table->dropColumn('caption');
            $table->dropColumn('amount');
        });
    }

    protected function createPlan($name, $quota, $price)
    {
        static $order_index = 1;
        $plan = new SubscriptionKind();
        $plan->name = $name;
        $plan->hours_quota = $quota;
        $plan->price = $price;
        $plan->order_index = $order_index++;
        $plan->save();
        return $plan;
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
