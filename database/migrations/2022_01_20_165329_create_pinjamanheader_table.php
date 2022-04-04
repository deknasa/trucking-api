<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinjamanheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinjamanheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('coa',50)->default('');
            $table->string('pengeluaran_nobukti',50)->default('');
            $table->date('tglkaskeluar',50)->default('1900/1/1');
            $table->integer('statusposting')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pengeluaran_nobukti')->references('nobukti')->on('pengeluaranheader');
            $table->foreign('coa')->references('coa')->on('akunpusat');
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
        Schema::dropIfExists('pinjamanheader');
    }
}
