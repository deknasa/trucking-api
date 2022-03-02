<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGajisupirpelunasanpinjamanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gajisupirpelunasanpinjaman', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gajisupir_id')->default(0);   
            $table->string('nobukti', 50)->default('');            
            $table->date('tgl')->default('1900/1/1');            
            $table->string('pinjaman_nobukti', 50)->default('');            
            $table->longText('keterangan')->default('');            
            $table->unsignedBigInteger('supir_id')->default(0);   
            $table->double('nominal', 15,2)->default(0);            
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();

            $table->foreign('gajisupir_id')->references('id')->on('gajisupirheader')->onDelete('cascade');            
            $table->foreign('pinjaman_nobukti')->references('nobukti')->on('pinjaman');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gajisupirpelunasanpinjaman');
    }
}
