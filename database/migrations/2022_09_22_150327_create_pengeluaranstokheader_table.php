<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluaranstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluaranstokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti',50)->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->unsignedBigInteger('supplier_id')->default('0');
            $table->string('pengeluaranstok_nobukti',50)->default('');
            $table->string('penerimaanstok_nobukti',50)->default('');
            $table->string('sin_nobukti',50)->unique();
            $table->unsignedBigInteger('kerusakan_id')->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('trado_id')->references('id')->on('trado');
            $table->foreign('gudang_id')->references('id')->on('gudang');
            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('supplier_id')->references('id')->on('supplier');
            $table->foreign('kerusakan_id')->references('id')->on('kerusakan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranstokheader');
    }
}
