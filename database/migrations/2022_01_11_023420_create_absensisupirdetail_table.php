<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateabsensisupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absensisupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('absensi_id')->default(0);
            $table->string('nobukti', 50)->default('');
            $table->unsignedBigInteger('trado_id')->default(0);
            $table->unsignedBigInteger('supir_id')->default(0);
            $table->longText('keterangan')->default('');
            $table->double('uangjalan', 15,2)->default(0);
            $table->unsignedBigInteger('absen_id')->default(0);
            $table->time('jam')->default('');
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('absensi_id')->references('id')->on('absensisupirheader')->onDelete('cascade');
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
        Schema::dropIfExists('absensisupirdetail');
    }
}
