<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePelunasanpiutangdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pelunasanpiutangdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelunasanpiutang_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->string('piutang_nobukti',50)->default('');
            $table->integer('cicilan')->length(11)->default('0');
            $table->date('tglcair')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->date('tgljt')->default('1900/1/1');
            $table->double('penyesuaian',15,2)->default('0');
            $table->string('coapenyesuaian',50)->default('');
            $table->string('invoice_nobukti',50)->default('');
            $table->longText('keteranganpenyesuaian')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pelunasanpiutang_id')->references('id')->on('pelunasanpiutangheader')->onDelete('cascade');                                    
            $table->foreign('invoice_nobukti')->references('nobukti')->on('invoiceheader');
            $table->foreign('coapenyesuaian')->references('coa')->on('akunpusat');
            $table->foreign('piutang_nobukti')->references('nobukti')->on('piutangheader');
            $table->foreign('agen_id')->references('id')->on('agen');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelunasanpiutangdetail');
    }
}
