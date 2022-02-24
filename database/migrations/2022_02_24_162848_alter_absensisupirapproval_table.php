<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAbsensisupirapprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('absensisupirapproval', function ($table) {
            $table->foreign('trado_id')->references('id')->on('trado');
            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('supirserap_id')->references('id')->on('supir');
            $table->foreign('nobukti_absensi')->references('nobukti')->on('absensisupirheader');
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
