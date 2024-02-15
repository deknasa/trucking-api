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
            $table->longText('kodepengeluaran')->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->string('coadebet',50)->nullable();            
            $table->string('coakredit',50)->nullable();            
            $table->string('coapostingdebet',50)->nullable();            
            $table->string('coapostingkredit',50)->nullable();           
            $table->unsignedBigInteger('format')->nullable();          
            $table->integer('jenisorder_id')->nullable();          
            $table->unsignedBigInteger('aco_id')->nullable();          
            $table->unsignedBigInteger('cabang_id')->nullable();          
            $table->integer('statusaktif')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();              
            $table->timestamps();

            $table->foreign('coadebet', 'pengeluarantrucking_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coakredit', 'pengeluarantrucking_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coapostingdebet', 'pengeluarantrucking_akunpusat_coapostingdebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coapostingkredit', 'pengeluarantrucking_akunpusat_coapostingkredit_foreign')->references('coa')->on('akunpusat');
        });

        DB::statement("ALTER TABLE pengeluarantrucking NOCHECK CONSTRAINT pengeluarantrucking_akunpusat_coadebet_foreign");
        DB::statement("ALTER TABLE pengeluarantrucking NOCHECK CONSTRAINT pengeluarantrucking_akunpusat_coakredit_foreign");
        DB::statement("ALTER TABLE pengeluarantrucking NOCHECK CONSTRAINT pengeluarantrucking_akunpusat_coapostingdebet_foreign");
        DB::statement("ALTER TABLE pengeluarantrucking NOCHECK CONSTRAINT pengeluarantrucking_akunpusat_coapostingkredit_foreign");
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
