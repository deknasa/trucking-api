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
        Schema::table('kasgantungheader', function ($table) {
            $table->foreign('penerima_id')->references('id')->on('penerima');
            $table->foreign('bank_id')->references('id')->on('bank');
            $table->foreign('nobuktikaskeluar')->references('nobukti')->on('pengeluaranheader');
            $table->foreign('coakaskeluar')->references('coa')->on('akunpusat');
            $table->foreign('nobukti')->references('nobukti')->on('jurnalumumheader');
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
