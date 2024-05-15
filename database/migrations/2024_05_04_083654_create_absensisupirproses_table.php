<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensisupirprosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absensisupirproses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('absensi_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->unsignedBigInteger('statusjeniskendaraan')->nullable();            
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('absensi_id', 'absensisupirproses_absensisupirheader_absensi_id_foreign')->references('id')->on('absensisupirheader')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absensisupirproses');
    }
}
