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
            $table->unsignedBigInteger('pengembaliankasbank_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('nowarkat',50)->default('');
            $table->date('tgljatuhtempo')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('coadebet',50)->default('');
            $table->string('coakredit',50)->default('');
            $table->longText('keterangan')->default('');
            $table->date('bulanbeban')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');            
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
