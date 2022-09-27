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
            $table->unsignedBigInteger('gajisupir_id')->default(0);   
            $table->string('nobukti', 50)->default('');            
            $table->double('nominaldeposito', 15,2)->default(0);            
            $table->double('nourut', 15,2)->default(0);            
            $table->string('suratpengantar_nobukti', 50)->default('');            
            $table->double('komisisupir', 15,2)->default(0);            
            $table->double('tolsupir', 15,2)->default(0);            
            $table->double('voucher', 15,2)->default(0);            
            $table->string('novoucher', 50)->default('');            
            $table->double('gajisupir', 15,2)->default(0);            
            $table->double('gajikenek', 15,2)->default(0);            
            $table->double('gajiritasi', 15,2)->default(0);            
            $table->double('nominalpengembalianpinjaman', 15,2)->default(0);            
            $table->string('modifiedby', 50)->default('');            
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
