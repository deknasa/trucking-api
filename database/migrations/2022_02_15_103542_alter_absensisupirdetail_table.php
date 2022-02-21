<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAbsensisupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('absensisupirdetail', function ($table) {
            $table->foreign('trado_id')->references('id')->on('trado');
            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('absen_id')->references('id')->on('absentrado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
              
    }
}
