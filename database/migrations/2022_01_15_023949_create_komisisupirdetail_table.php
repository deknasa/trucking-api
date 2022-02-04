<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKomisisupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('komisisupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('komisisupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('gaji_nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->date('tgldari')->default('1900/1/1');
            $table->date('tglsampai')->default('1900/1/1');
            $table->double('total',15,2)->default('0');
            $table->double('potonganpinjaman',15,2)->default('0');
            $table->double('deposito',15,2)->default('0');
            $table->double('potonganpinjamansemua',15,2)->default('0');
            $table->double('komisisupir',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('komisisupir_id')->references('id')->on('komisisupirheader')->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('komisisupirdetail');
    }
}
