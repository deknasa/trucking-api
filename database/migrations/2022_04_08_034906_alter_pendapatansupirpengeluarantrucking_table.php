<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPendapatansupirpengeluarantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pendapatansupirpengeluarantrucking', function (Blueprint $table) {
            $table->foreign('nobukti', 'pendapatansupirpengeluarantrucking_pengeluarantrucking_nobukti_foreign')->references('nobukti')->on('pengeluarantruckingheader');
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
