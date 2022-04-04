<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProsesgajisupirpelunasanpinjamanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prosesgajisupirpelunasanpinjaman', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosesgajisupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->string('pinjaman_nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('prosesgajisupir_id')->references('id')->on('prosesgajisupirheader')->onDelete('cascade');                         

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesgajisupirpelunasanpinjaman');
    }
}
