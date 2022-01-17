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
            $table->string('nobukti', 50)->default('');            
            $table->unsignedBigInteger('gajisupir_id')->default(0);   
            $table->date('tgl')->default('1900/1/1');            
            $table->string('pinjaman_nobukti', 50)->default('');            
            $table->string('keterangan', 250)->default('');            
            $table->unsignedBigInteger('supir_id')->default(0);   
            $table->string('modifiedby', 50)->default('');            
            $table->double('nominal', 15,2)->default(0);            
            $table->timestamps();

            $table->foreign('gajisupir_id')->references('id')->on('gajisupirheader')->onDelete('cascade');            

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
