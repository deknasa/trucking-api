<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGajisupirpelunasanpinjaman2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gajisupirpelunasanpinjaman', function (Blueprint $table) {
            $table->foreign('pengembalianpinjaman_nobukti')->references('nobukti')->on('pengembalianpinjamanheader');
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
