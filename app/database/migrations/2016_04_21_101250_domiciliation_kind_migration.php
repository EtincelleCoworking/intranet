<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DomiciliationKindMigration extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domiciliation_kind', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->decimal('price');

            $table->timestamps();
        });
        $items = array();
        $items['Domiciliation commerciale'] = 35;
        $items['Domiciliation commerciale avec renvoi de courrier mensuel'] = 55;
        $items['Domiciliation commerciale avec renvoi de courrier bi-mensuel'] = 75;
        $items['Domiciliation commerciale avec renvoi de courrier hebdomadaire'] = 115;
        $items['Domiciliation commerciale avec renvoi de courrier quotidien'] = 195;
        foreach ($items as $name => $price) {
            $dk = new DomiciliationKind();
            $dk->name = $name;
            $dk->price = $price;
            $dk->save();
            $items[$name] = $dk;
        }

        Schema::table('organisations', function (Blueprint $table) {
            $table->integer('domiciliation_kind_id')->unsigned()->nullable();
            $table->foreign('domiciliation_kind_id')
                ->references('id')->on('domiciliation_kind')
                ->onDelete('SET NULL');
            $table->date('domiciliation_start_at')->nullable();
            $table->date('domiciliation_end_at')->nullable();
        });

        foreach (Organisation::where('is_domiciliation', 1)->get() as $organisation) {
            $organisation->domiciliation_kind_id = $items['Domiciliation commerciale']->id;
            $organisation->save();
        }

        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('is_domiciliation');
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
