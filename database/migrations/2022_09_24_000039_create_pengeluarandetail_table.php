<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluarandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengeluarandetail');
        
        Schema::create('pengeluarandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluaran_id')->nullable();
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
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();


            $table->foreign('pengeluaran_id', 'pengeluarandetail_pengeluaranheader_pengeluaran_id_foreign')->references('id')->on('pengeluaranheader')->onDelete('cascade');       
            $table->foreign('coadebet', 'pengeluarandetail_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coakredit', 'pengeluarandetail_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');


        });

        DB::statement("ALTER TABLE pengeluarandetail NOCHECK CONSTRAINT pengeluarandetail_akunpusat_coadebet_foreign");
        DB::statement("ALTER TABLE pengeluarandetail NOCHECK CONSTRAINT pengeluarandetail_akunpusat_coakredit_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarandetail');
    }
}
