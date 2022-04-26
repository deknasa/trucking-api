<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPenerimaantruckingdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penerimaantruckingdetail', function (Blueprint $table) {
             $table->foreign('pengeluarantruckingheader_nobukti')->references('nobukti')->on('pengeluarantruckingheader')->disableForeignKeyConstraints();
        });
        

        // Schema::enableForeignKeyConstraints();
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
