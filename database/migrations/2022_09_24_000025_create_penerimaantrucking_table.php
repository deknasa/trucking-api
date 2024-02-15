<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('penerimaantrucking');

        Schema::create('penerimaantrucking', function (Blueprint $table) {
            $table->id();
            $table->longText('kodepenerimaan')->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->string('coadebet',50)->nullable();            
            $table->string('coakredit',50)->nullable();            
            $table->string('coapostingdebet',50)->nullable();            
            $table->string('coapostingkredit',50)->nullable();            
            $table->unsignedBigInteger('format')->nullable();            
            $table->unsignedBigInteger('aco_id')->nullable();          
            $table->unsignedBigInteger('cabang_id')->nullable();          
            $table->integer('statusaktif')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();              
            $table->timestamps();

            $table->foreign('coadebet', 'penerimaantrucking_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coakredit', 'penerimaantrucking_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coapostingdebet', 'penerimaantrucking_akunpusat_coapostingdebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coapostingkredit', 'penerimaantrucking_akunpusat_coapostingkredit_foreign')->references('coa')->on('akunpusat');
        });

        DB::statement("ALTER TABLE penerimaantrucking NOCHECK CONSTRAINT penerimaantrucking_akunpusat_coadebet_foreign");
        DB::statement("ALTER TABLE penerimaantrucking NOCHECK CONSTRAINT penerimaantrucking_akunpusat_coakredit_foreign");
        DB::statement("ALTER TABLE penerimaantrucking NOCHECK CONSTRAINT penerimaantrucking_akunpusat_coapostingdebet_foreign");
        DB::statement("ALTER TABLE penerimaantrucking NOCHECK CONSTRAINT penerimaantrucking_akunpusat_coapostingkredit_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaantrucking');
    }
}
