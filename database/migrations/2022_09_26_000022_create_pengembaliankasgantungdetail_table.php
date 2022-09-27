<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengembaliankasgantungdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengembaliankasgantungdetail');

        Schema::create('pengembaliankasgantungdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengembaliankasgantung_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('coa',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->string('kasgantung_nobukti',50)->default('');
            $table->timestamps();

            $table->foreign('pengembaliankasgantung_id', 'pengembaliankasgantungdetail_pengembaliankasgantungheader_pengembaliankasgantung_id_foreign')->references('id')->on('pengembaliankasgantungheader')->onDelete('cascade');    
            $table->foreign('kasgantung_nobukti', 'penerimaandetail_kasgantungheader_kasgantung_nobukti_foreign')->references('nobukti')->on('kasgantungheader');    
            $table->foreign('coa', 'penerimaandetail_akunpusat_coa_foreign')->references('coa')->on('akunpusat');    




        });

        DB::statement("ALTER TABLE pengembaliankasgantungdetail NOCHECK CONSTRAINT penerimaandetail_kasgantungheader_kasgantung_nobukti_foreign");
        DB::statement("ALTER TABLE pengembaliankasgantungdetail NOCHECK CONSTRAINT penerimaandetail_akunpusat_coa_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengembaliankasgantungdetail');
    }
}
