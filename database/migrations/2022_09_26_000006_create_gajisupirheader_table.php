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
            $table->date('tglbukti')->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('supir_id')->nullable();            
            $table->double('nominal',15,2)->nullable();            
            $table->date('tgldari')->nullable();            
            $table->date('tglsampai')->nullable();            
            $table->double('total',15,2)->nullable();            
            $table->double('uangjalan',15,2)->nullable();            
            $table->double('bbm',15,2)->nullable();            
            $table->double('potonganpinjaman',15,2)->nullable();            
            $table->double('deposito',15,2)->nullable();            
            $table->double('potonganpinjamansemua',15,2)->nullable();            
            $table->double('komisisupir',15,2)->nullable();            
            $table->double('tolsupir',15,2)->nullable();            
            $table->double('voucher',15,2)->nullable();            
            $table->double('uangmakanberjenjang',15,2)->nullable();            
            $table->double('uangmakanharian',15,2)->nullable();            
            $table->double('pinjamanpribadi',15,2)->nullable();            
            $table->double('gajiminus',15,2)->nullable();            
            $table->double('uangJalantidakterhitung',15,2)->nullable();            
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();

            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();            
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
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
