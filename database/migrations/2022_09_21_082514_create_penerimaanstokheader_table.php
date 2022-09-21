<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaanstokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');            
            $table->unsignedBigInteger('penerimaanstok_id')->default(0);
            $table->string('penerimaanstok_nobukti',50)->default('');
            $table->string('pengeluaranstok_nobukti',50)->default('');
            $table->unsignedBigInteger('supplier_id')->default(0);            
            $table->unsignedBigInteger('statushitungstok')->default(0);
            $table->string('nobon', 50)->default('');
            $table->string('hutang_nobukti', 50)->default('');
            $table->unsignedBigInteger('gudangdari_id')->default('0');
            $table->unsignedBigInteger('gudangke_id')->default('0');            
            $table->string('coa',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaanstok_id')->references('id')->on('penerimaanstok');
            $table->foreign('coa')->references('coa')->on('akunpusat');
            $table->foreign('supplier_id')->references('id')->on('supplier');
            $table->foreign('hutang_nobukti')->references('nobukti')->on('hutangheader');
            $table->foreign('gudangdari_id')->references('id')->on('gudang');
            $table->foreign('gudangke_id')->references('id')->on('gudang');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaanstokheader');
    }
}
