<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengembalianpinjamandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengembalianpinjamandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengembalianpinjaman_id');
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('nobukti_pinjaman',50)->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');               
            $table->timestamps();

            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('pengembalianpinjaman_id')->references('id')->on('pengembalianpinjamanheader')->onDelete('cascade');             
            $table->foreign('nobukti_pinjaman')->references('nobukti')->on('pinjaman');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengembalianpinjamandetail');
    }
}
