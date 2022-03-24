<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('nowarkat',50)->default('');
            $table->date('tgljatuhtempo')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('coadebet',50)->default('');
            $table->string('coakredit',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->string('invoice_nobukti',50)->default('');
            $table->unsignedBigInteger('bankpelanggan_id')->default('0');
            $table->string('jenisbiaya',50)->default('');
            $table->string('penerimaanpiutang_nobukti',50)->default('');
            $table->date('bulanbeban')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaan_id')->references('id')->on('penerimaanheader')->onDelete('cascade');                                                
            $table->foreign('pelanggan_id')->references('id')->on('pelanggan');
            $table->foreign('bankpelanggan_id')->references('id')->on('bankpelanggan');
            $table->foreign('bank_id')->references('id')->on('bank');
            $table->foreign('coadebet')->references('coa')->on('akunpusat');
            $table->foreign('coakredit')->references('coa')->on('akunpusat');
            $table->foreign('penerimaanpiutang_nobukti')->references('nobukti')->on('pelunasanpiutangheader');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaandetail');
    }
}
