<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProsesAbsensiSupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prosesabsensisupir', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->longText('keterangan', 8000)->default('');
            $table->string('pengeluaran_nobukti', 50)->unique();
            $table->string('absensisupir_nobukti', 50)->unique();
            $table->double('nominal',15,2)->default(0);
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('pengeluaran_nobukti')->references('nobukti')->on('pengeluaranheader');
            $table->foreign('absensisupir_nobukti')->references('nobukti')->on('absensisupirheader');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesabsensisupir');
    }
}
