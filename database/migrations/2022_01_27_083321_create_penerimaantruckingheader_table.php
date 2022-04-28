<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaantruckingheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaantruckingheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');            
            $table->unsignedBigInteger('penerimaantrucking_id')->default(0);
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('coa',50)->default('');
            $table->string('penerimaan_nobukti',50)->default('');
            $table->date('penerimaan_tgl')->default('1900/1/1');            
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaantrucking_id')->references('id')->on('penerimaantrucking');
            $table->foreign('bank_id')->references('id')->on('bank');
            $table->foreign('coa')->references('coa')->on('akunpusat');
            $table->foreign('penerimaan_nobukti')->references('nobukti')->on('penerimaanheader');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaantruckingheader');
    }
}
