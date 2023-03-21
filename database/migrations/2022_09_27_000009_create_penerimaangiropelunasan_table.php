<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaangiropelunasanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaangiropelunasan');

        Schema::create('penerimaangiropelunasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaangiro_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('pelunasanpiutang_nobukti',50)->nullable();
            $table->date('tglterima')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();


            $table->foreign('penerimaangiro_id', 'penerimaangiropelunasan_penerimaangiroheader_penerimaangiro_id_foreign')->references('id')->on('penerimaangiroheader')->onDelete('cascade');    
            $table->foreign('pelunasanpiutang_nobukti', 'penerimaangiropelunasan_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign')->references('nobukti')->on('pelunasanpiutangheader');


        });

        DB::statement("ALTER TABLE penerimaangiropelunasan NOCHECK CONSTRAINT penerimaangiropelunasan_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaangiropelunasan');
    }
}
