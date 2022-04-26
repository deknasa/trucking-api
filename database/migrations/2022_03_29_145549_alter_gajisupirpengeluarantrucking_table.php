<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGajisupirpengeluarantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gajisupirpengeluarantrucking', function (Blueprint $table) {
            $table->foreign('pengeluarantrucking_nobukti','gajisupirpengeluarantrucking_pengeluarantruckingheader_nobukti_foreign')->references('nobukti')->on('pengeluarantruckingheader');
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
