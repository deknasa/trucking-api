<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluarantruckingheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluarantruckingheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');            
            $table->unsignedBigInteger('pengeluarantrucking_id')->default(0);
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->integer('statusposting')->length(11)->default('0');
            $table->string('coa',50)->default('');
            $table->string('pengeluaran_nobukti',50)->default('');
            $table->date('pengeluaran_tgl')->default('1900/1/1');               
            $table->string('modifiedby',50)->default('');

            $table->timestamps();

            $table->foreign('pengeluarantrucking_id')->references('id')->on('pengeluarantrucking');
            $table->foreign('bank_id')->references('id')->on('bank');
            $table->foreign('coa')->references('coa')->on('akunpusat');
            $table->foreign('pengeluaran_nobukti')->references('nobukti')->on('pengeluaranheader');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarantruckingheader');
    }
}
