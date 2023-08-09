<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateGajisupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('gajisupirdetail');

        Schema::create('gajisupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gajisupir_id')->nullable();   
            $table->string('nobukti', 50)->nullable();            
            $table->double('nominaldeposito', 15,2)->nullable();            
            $table->double('nourut', 15,2)->nullable();            
            $table->string('suratpengantar_nobukti', 50)->nullable();            
            $table->string('ritasi_nobukti', 50)->nullable();            
            $table->double('komisisupir', 15,2)->nullable();            
            $table->double('tolsupir', 15,2)->nullable();            
            $table->double('voucher', 15,2)->nullable();            
            $table->string('novoucher', 50)->nullable();            
            $table->double('gajisupir', 15,2)->nullable();            
            $table->double('gajikenek', 15,2)->nullable();            
            $table->double('gajiritasi', 15,2)->nullable();            
            $table->double('biayatambahan', 15,2)->nullable();            
            $table->double('uangmakanberjenjang',15,2)->nullable();            
            $table->longText('keteranganbiayatambahan')->nullable();            
            $table->double('nominalpengembalianpinjaman', 15,2)->nullable();            
            $table->string('modifiedby', 50)->nullable();            
            $table->timestamps();

            $table->foreign('gajisupir_id', 'gajisupirdetail_gajisupirheader_gajisupir_id_foreign')->references('id')->on('gajisupirheader')->onDelete('cascade');    



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gajisupirdetail');
    }
}
