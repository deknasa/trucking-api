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
            $table->string('nobukti', 50)->default('');            
            $table->date('tgl')->default('1900/1/1');            
            $table->unsignedBigInteger('supir_id')->default(0);            
            $table->double('nominal',15,2)->default(0);            
            $table->string('keterangan', 250)->default('');            
            $table->date('tgldr')->default('1900/1/1');            
            $table->date('tglsd')->default('1900/1/1');            
            $table->double('total',15,2)->default(0);            
            $table->double('ujalan',15,2)->default(0);            
            $table->double('ubbm',15,2)->default(0);            
            $table->double('potpinjaman',15,2)->default(0);            
            $table->double('deposit',15,2)->default(0);            
            $table->double('potpinjamansemua',15,2)->default(0);            
            $table->double('komisisupir',15,2)->default(0);            
            $table->double('tolsupir',15,2)->default(0);            
            $table->double('uvoucher',15,2)->default(0);            
            $table->double('umakanharian',15,2)->default(0);            
            $table->double('pinjamanpribadi',15,2)->default(0);            
            $table->double('gajiminus',15,2)->default(0);            
            $table->double('uJalantidakterhitung',15,2)->default(0);            
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
