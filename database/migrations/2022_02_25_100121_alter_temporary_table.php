<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTemporaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengeluarandetail', function ($table) {
            $table->foreign('coadebet')->references('coa')->on('akunpusat');
            $table->foreign('coakredit')->references('coa')->on('akunpusat');
        });

        Schema::table('kasgantungheader', function ($table) {
            $table->foreign('coakaskeluar')->references('coa')->on('akunpusat');
        });

        Schema::table('bank', function ($table) {
            $table->foreign('coa')->references('coa')->on('akunpusat');
        });

        Schema::table('jurnalumumdetail', function ($table) {
            $table->foreign('coa')->references('coa')->on('akunpusat');
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
