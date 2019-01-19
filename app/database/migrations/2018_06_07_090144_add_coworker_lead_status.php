<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoworkerLeadStatus extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('users', function (Blueprint $table) {
            $table->integer('lead_status')->unsigned()->nullable();
            $table->string('lead_source')->nullable();
            $table->boolean('is_lead')->default(true);
            $table->dateTime('lead_contacted_at')->nullable();
            $table->dateTime('lead_toured_at')->nullable();
            $table->dateTime('lead_tried_at')->nullable();
            $table->dateTime('lead_closed_at')->nullable();
            $table->string('lead_lost_reason', 255)->nullable();
        });


        // user.modify
        // - [x] Prospect
        // - champs dates pour les différentes étapes

        // user.leads
        // Ville, Nom, Statut, Contacté, Rdv pris, Visité le, Journée d'essai le, Clos le
        // Filtre: Checkboxes pour les statuts, espace
        // Actions: en fonction du statut - next step / lost

        // Stats?
        // - nb de prospects par mois / semaine
        // - taux de transformation
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
