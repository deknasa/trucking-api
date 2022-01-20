<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGajisupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gajisupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();            
            $table->date('tgl')->default('1900/1/1');            
            $table->unsignedBigInteger('supir_id')->default(0);            
            $table->double('nominal',15,2)->default(0);            
            $table->string('keterangan', 250)->default('');            
            $table->date('tgldari')->default('1900/1/1');            
            $table->date('tglsampai')->default('1900/1/1');            
            $table->double('total',15,2)->default(0);            
            $table->double('uangjalan',15,2)->default(0);            
            $table->double('bbm',15,2)->default(0);            
            $table->double('potonganpinjaman',15,2)->default(0);            
            $table->double('deposito',15,2)->default(0);            
            $table->double('potonganpinjamansemua',15,2)->default(0);            
            $table->double('komisisupir',15,2)->default(0);            
            $table->double('tolsupir',15,2)->default(0);            
            $table->double('voucher',15,2)->default(0);            
            $table->double('uangmakanharian',15,2)->default(0);            
            $table->double('pinjamanpribadi',15,2)->default(0);            
            $table->double('gajiminus',15,2)->default(0);            
            $table->double('uangJalantidakterhitung',15,2)->default(0);            
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gajisupirheader');
    }
}
