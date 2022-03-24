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
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();

            $table->foreign('gajisupir_id')->references('id')->on('gajisupirheader')->onDelete('cascade');            
            $table->foreign('trip_nobukti')->references('nobukti')->on('suratpengantar');


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
