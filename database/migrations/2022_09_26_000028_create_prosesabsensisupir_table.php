<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProsesAbsensiSupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('prosesabsensisupir');

        Schema::create('prosesabsensisupir', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan', 8000)->default('');
            $table->string('pengeluaran_nobukti', 50)->unique();
            $table->string('absensisupir_nobukti', 50)->unique();
            $table->double('nominal',15,2)->default(0);
            $table->unsignedBigInteger('statusformat')->default(0);            
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();


            $table->foreign('absensisupir_nobukti', 'prosesabsensisupir_absensisupirheader_absensisupir_nobukti_foreign')->references('nobukti')->on('absensisupirheader');   
            $table->foreign('pengeluaran_nobukti', 'prosesabsensisupir_pengeluaranheader_pengeluaran_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');   
        });

        DB::statement("ALTER TABLE prosesabsensisupir NOCHECK CONSTRAINT prosesabsensisupir_absensisupirheader_absensisupir_nobukti_foreign");
        DB::statement("ALTER TABLE prosesabsensisupir NOCHECK CONSTRAINT prosesabsensisupir_pengeluaranheader_pengeluaran_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesabsensisupir');
    }
}
