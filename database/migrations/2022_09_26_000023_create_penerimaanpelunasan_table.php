<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaanpelunasanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaanpelunasan');

        Schema::create('penerimaanpelunasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('pelunasanpiutang_nobukti',50)->default('');
            $table->date('tglterima')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();


            $table->foreign('penerimaan_id', 'penerimaanpelunasan_penerimaanheader_penerimaan_id_foreign')->references('id')->on('penerimaanheader')->onDelete('cascade');    
            $table->foreign('pelunasanpiutang_nobukti', 'penerimaanpelunasan_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign')->references('nobukti')->on('pelunasanpiutangheader');    


        });

        DB::statement("ALTER TABLE penerimaanpelunasan NOCHECK CONSTRAINT penerimaanpelunasan_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaanpelunasan');
    }
}
