<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGajisupirpenerimaantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gajisupirpenerimaantrucking', function (Blueprint $table) {
            $table->foreign('penerimaantrucking_nobukti','gajisupirpenerimaantrucking_penerimaantruckingheader_nobukti_foreign')->references('nobukti')->on('penerimaantruckingheader');
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
