<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CashflowSetupMigration extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashflow_account', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->decimal('amount');
        });

        Schema::create('cashflow_operation', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->date('occurs_at');
            $table->decimal('amount');
            $table->string('name');
            $table->string('frequency');
            $table->boolean('archived');

            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')
                ->references('id')->on('cashflow_account')
                ->onDelete('CASCADE');
        });

        $account = CashflowAccount::build('Banque Populaire Occitane', 7300);
        // Staff
        $account->createOperation('2017-03-01', -1500, 'Salaire Aurélie %month.last%', '+1 month');
        $account->createOperation('2017-03-01', -1300 * 1.2, 'Salaire Sarah %month.last%', '+1 month');
        $account->createOperation('2017-03-01', -500, 'Salaire Matthieu %month.last%', '+1 month');
        $account->createOperation('2017-03-01', -3000, 'Rémunération Sébastien %month.last%', '+1 month');
        // Banques
        $account->createOperation('2017-02-19', -588, 'Prêt BPOC', '+1 month');
        $account->createOperation('2017-02-25', -585.14, 'Prêt BPOC', '+1 month');
        $account->createOperation('2017-02-26', -171, 'Prêt BPOC', '+1 month');
        // Wilson
        $groupName = 'Wilson - ';
        $account->createOperation('2017-03-01', -3127.96, $groupName . 'Loyer %month%', '+1 month');
        $account->createOperation('2017-03-01', -254.55, $groupName . 'Charges %month%', '+1 month');
        $account->createOperation('2017-03-01', -253 * 1.2, $groupName . 'Nettoyage %month.last%', '+1 month');
        $account->createOperation('2017-10-15', -4000, $groupName . 'Taxe foncière %year%', '+1 year');
        // Carmes
        $groupName = 'Carmes - ';
        $account->createOperation('2017-03-01', -3041 * 1.2, $groupName . 'Loyer %month%', '+1 month');
        $account->createOperation('2017-03-01', -363 * 1.2, $groupName . 'Taxe foncière %month%', '+1 month');
        $account->createOperation('2017-03-01', -200 * 1.2, $groupName . 'Charges %month%', '+1 month');
        $account->createOperation('2017-03-01', -1468 * 1.2, $groupName . 'Nettoyage %month.last%', '+1 month');
        // Victor Hugo
        $groupName = 'Victor Hugo - ';
        $account->createOperation('2017-03-01', -1680 * 1.2, $groupName . 'Loyer %month%', '+1 month');
        $account->createOperation('2017-03-01', -75 * 1.2, $groupName . 'Charges %month%', '+1 month');
        $account->createOperation('2017-03-01', -305 * 1.2, $groupName . 'Taxe foncière %month%', '+1 month');
        $account->createOperation('2017-03-01', -478 * 1.2, $groupName . 'Nettoyage %month.last%', '+1 month');
        // Montauban
        $groupName = 'Montauban - ';
        $account->createOperation('2017-03-01', -1150 * 1.2, $groupName . 'Loyer %month%', '+1 month');
        $account->createOperation('2017-03-01', -850 * 1.2, $groupName . 'Charges %month%', '+1 month');
        // Albi
        $groupName = 'Albi - ';
//        $account->createOperation('2017-03-01', -1150 * 1.2, $groupName . 'Loyer %month%', '+1 month');
//        $account->createOperation('2017-03-01', -850 * 1.2, $groupName . 'Charges %month%', '+1 month');

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
