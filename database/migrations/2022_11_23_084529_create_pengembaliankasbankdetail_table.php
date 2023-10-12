<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengembaliankasbankdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengembaliankasbankdetail');

        Schema::create('pengembaliankasbankdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengembaliankasbank_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('nowarkat',50)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('coadebet',50)->nullable();
            $table->string('coakredit',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->date('bulanbeban')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('pengembaliankasbank_id', 'pengembaliankasbankdetail_pengembaliankasbankheader_pengeluaran_id_foreign')->references('id')->on('pengembaliankasbankheader')->onDelete('cascade'); 
            $table->foreign('coadebet', 'pengembaliankasbankdetail_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coakredit', 'pengembaliankasbankdetail_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');

        });

        DB::statement("ALTER TABLE pengembaliankasbankdetail NOCHECK CONSTRAINT pengembaliankasbankdetail_akunpusat_coadebet_foreign");
        DB::statement("ALTER TABLE pengembaliankasbankdetail NOCHECK CONSTRAINT pengembaliankasbankdetail_akunpusat_coakredit_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengembaliankasbankdetail');
    }
}
