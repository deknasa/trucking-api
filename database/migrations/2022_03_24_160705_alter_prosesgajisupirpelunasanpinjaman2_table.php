<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProsesgajisupirpelunasanpinjaman2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prosesgajisupirpelunasanpinjaman', function (Blueprint $table) {
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
