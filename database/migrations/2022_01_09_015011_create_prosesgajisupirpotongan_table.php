<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProsesgajisupirpotonganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prosesgajisupirpotongan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosesgajisupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');            
            $table->unsignedBigInteger('potongan_id')->default('0');
            $table->double('nominalpotongan',15,2)->default('0');
            $table->integer('postingpotongan')->length(11)->default('');
            $table->string('pengeluaran_nobukti',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('prosesgajisupir_id')->references('id')->on('prosesgajisupirheader')->onDelete('cascade');                         
            $table->foreign('potongan_id')->references('id')->on('potongangajisupir');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesgajisupirpotongan');
    }
}
