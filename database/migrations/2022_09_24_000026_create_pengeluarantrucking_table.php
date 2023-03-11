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
            $table->string('coadebet',50)->default('');            
            $table->string('coakredit',50)->default('');            
            $table->string('coapostingdebet',50)->default('');            
            $table->string('coapostingkredit',50)->default('');           
            $table->unsignedBigInteger('format')->default(0);          
            $table->string('modifiedby',50)->default('');              
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
