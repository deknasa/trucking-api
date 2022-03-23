<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProsesgajisupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prosesgajisupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosesgajisupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('gajisupir_nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('prosesgajisupir_id')->references('id')->on('prosesgajisupirheader')->onDelete('cascade');             
            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('trado_id')->references('id')->on('trado');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesgajisupirdetail');
    }
}
