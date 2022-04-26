<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterJurnalumumheader3Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jurnalumumheader', function (Blueprint $table) {
            $table->foreign('nobukti', 'piutangheader_jurnalumumheader_nobukti_foreign')->references('nobukti')->on('piutangheader');
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
