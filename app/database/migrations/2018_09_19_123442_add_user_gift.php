<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserGift extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_kind',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->string('code');
                $table->string('description');
            }
        );
        Schema::create('user_gift',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('CASCADE');

                $table->integer('kind_id')->unsigned();
                $table->foreign('kind_id')
                    ->references('id')->on('gift_kind')
                    ->onDelete('CASCADE');

                $table->dateTime('used_at')->nullable();
            }
        );
        Schema::create('gift_photoshoot_session',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->date('occurs_at');
                $table->integer('location_id')->unsigned();
                $table->foreign('location_id')
                    ->references('id')->on('locations')
                    ->onDelete('CASCADE');
            }
        );
        Schema::create('gift_photoshoot_slot',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->time('start_at');

                $table->integer('session_id')->unsigned();
                $table->foreign('session_id')
                    ->references('id')->on('gift_photoshoot_session')
                    ->onDelete('CASCADE');

                $table->integer('user_id')->unsigned()->nullable();
                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('CASCADE');
            }
        );

        /*
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('gift_photoshoot_slot')->truncate();
        DB::table('gift_photoshoot_session')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
*/
        $dates = array();
        $dates[] = '2018-10-10 13:30';
        $dates[] = '2018-11-07 13:30';
        $dates[] = '2018-12-12 13:30';
        $duration = 30;
        $count = 8;

        foreach ($dates as $date) {
            $session = new GiftPhotoshootSession();
            $session->occurs_at = $date;
            $session->location_id = 1;
            $session->save();
            for ($i = 0; $i < $count; $i++) {
                $gift = new GiftPhotoshootSlot();
                $gift->session_id = $session->id;
                $gift->start_at = date('H:i', strtotime(sprintf('+%d minutes', $duration * $i), strtotime($date)));;
                $gift->save();
            }
        }

        $kind = new GiftKind();
        $kind->code = GiftKind::PHOTOSHOOT;
        $kind->description = 'Shooting Photo';
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
