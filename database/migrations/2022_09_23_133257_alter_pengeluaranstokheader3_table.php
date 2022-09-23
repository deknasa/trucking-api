<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPengeluaranstokheader3Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengeluaranstokheader', function (Blueprint $table) {
            $table->unsignedBigInteger('pengeluaranstok_id')->default('0');
            $table->string('servicein_nobukti',50)->default('');

            $table->foreign('pengeluaranstok_id')->references('id')->on('pengeluaranstok');
            $table->foreign('servicein_nobukti')->references('nobukti')->on('serviceinheader');


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
