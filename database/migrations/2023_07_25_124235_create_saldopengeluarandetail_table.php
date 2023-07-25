<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSaldopengeluarandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldopengeluarandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saldopengeluaran_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('nowarkat',50)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('coadebet',50)->nullable();
            $table->string('coakredit',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('noinvoice',150)->nullable();
            $table->string('bank',150)->nullable();
            $table->date('bulanbeban')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('saldopengeluaran_id', 'saldopengeluarandetail_saldopengeluaranheader_pengeluaran_id_foreign')->references('id')->on('saldopengeluaranheader')->onDelete('cascade');       
            $table->foreign('coadebet', 'saldopengeluarandetail_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coakredit', 'saldopengeluarandetail_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');
        });

        DB::statement("ALTER TABLE saldopengeluarandetail NOCHECK CONSTRAINT saldopengeluarandetail_akunpusat_coadebet_foreign");
        DB::statement("ALTER TABLE saldopengeluarandetail NOCHECK CONSTRAINT saldopengeluarandetail_akunpusat_coakredit_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saldopengeluarandetail');
    }
}
