<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPenerimaanpelunasan1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penerimaanpelunasan', function (Blueprint $table) {
            $table->foreign('penerimaanpiutang_nobukti', 'penerimaanpelunasan_penerimaangiro_nobukti_foreign')->references('nobukti')->on('penerimaangiroheader');
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
