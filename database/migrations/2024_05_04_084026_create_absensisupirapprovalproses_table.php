<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensisupirapprovalprosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absensisupirapprovalproses', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('absensisupirapproval_id')->nullable();            
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('coakaskeluar', 50)->nullable();            
            $table->longText('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();      
            $table->longText('info')->nullable();
            $table->string('modifiedby', 200)->nullable();                  
            $table->timestamps();

            $table->foreign('absensisupirapproval_id', 'absensisupirapprovalproses_absensisupirheader_absensi_id_foreign')->references('id')->on('absensisupirapprovalheader')->onDelete('cascade');    

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absensisupirapprovalproses');
    }
}
