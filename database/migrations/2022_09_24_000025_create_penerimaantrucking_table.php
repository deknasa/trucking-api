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
            $table->longText('kodepenerimaan')->default('');            
            $table->longText('keterangan')->default('');            
            $table->string('coa',50)->default('');            
            $table->unsignedBigInteger('statusformat')->default(0);            
            $table->string('modifiedby',50)->default('');              
            $table->timestamps();

            $table->foreign('coa', 'penerimaantrucking_akunpusat_coa_foreign')->references('coa')->on('akunpusat');
        });

        DB::statement("ALTER TABLE penerimaantrucking NOCHECK CONSTRAINT penerimaantrucking_akunpusat_coa_foreign");
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
