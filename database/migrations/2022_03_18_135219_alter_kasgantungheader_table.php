<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterKasgantungheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kasgantungheader', function (Blueprint $table) {
            $table->foreign('nobuktikaskeluar')->references('nobukti')->on('pengeluaranheader');
            // $table->foreign('nobukti')->references('nobukti')->on('jurnalumumheader');
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
