<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengembaliankasgantungheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengembaliankasgantungheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->date('tgldari')->default('1900/1/1');
            $table->date('tglsampai')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pelanggan_id')->references('id')->on('pelanggan');
            $table->foreign('bank_id')->references('id')->on('bank');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengembaliankasgantungheader');
    }
}
