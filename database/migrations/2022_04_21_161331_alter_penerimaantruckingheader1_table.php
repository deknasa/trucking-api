<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPenerimaantruckingheader1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penerimaantruckingheader', function (Blueprint $table) {
            $table->foreign('proses_nobukti', 'penerimaantruckingheader_prosesgajisupirheader_nobukti_foreign')->references('nobukti')->on('prosesgajisupirheader');
            $table->foreign('proses_nobukti', 'penerimaantruckingheader_pendapatansupirheader_nobukti_foreign')->references('nobukti')->on('pendapatansupirheader');
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
