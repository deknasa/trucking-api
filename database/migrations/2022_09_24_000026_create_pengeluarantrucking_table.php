<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluarantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('pengeluarantrucking');

        Schema::create('pengeluarantrucking', function (Blueprint $table) {
            $table->id();
            $table->longText('kodepengeluaran')->default('');            
            $table->longText('keterangan')->default('');            
            $table->string('coa',50)->default('');            
            $table->unsignedBigInteger('statusformat')->default(0);          
            $table->string('modifiedby',50)->default('');              
            $table->timestamps();

            $table->foreign('coa', 'pengeluarantrucking_akunpusat_coa_foreign')->references('coa')->on('akunpusat');
        });

        DB::statement("ALTER TABLE pengeluarantrucking NOCHECK CONSTRAINT pengeluarantrucking_akunpusat_coa_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarantrucking');
    }
}
