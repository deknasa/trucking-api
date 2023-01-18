<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateGajisupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('gajisupirheader');
        
        Schema::create('gajisupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();            
            $table->date('tglbukti')->default('1900/1/1');            
            $table->unsignedBigInteger('supir_id')->default(0);            
            $table->double('nominal',15,2)->default(0);            
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
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();

            $table->foreign('supir_id', 'gajisupirheader_supir_supir_id_foreign')->references('id')->on('supir');


        });

        DB::statement("ALTER TABLE gajisupirheader NOCHECK CONSTRAINT gajisupirheader_supir_supir_id_foreign");

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
