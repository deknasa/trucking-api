<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreatePengeluaranstokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengeluaranstok');

        Schema::create('pengeluaranstok', function (Blueprint $table) {
            $table->id();
            $table->longText('kodepengeluaran')->default('');            
            $table->longText('keterangan')->default('');            
            $table->string('coa',50)->default('');            
            $table->unsignedBigInteger('statusformat')->default(0);   
            $table->integer('statushitungstok')->length(11)->default(0);                        
            $table->string('modifiedby',50)->default('');                
            $table->timestamps();

            $table->foreign('coa', 'pengeluaranstok_akunpusat_coa_foreign')->references('coa')->on('akunpusat');  
        });
        DB::statement("ALTER TABLE pengeluaranstok NOCHECK CONSTRAINT pengeluaranstok_akunpusat_coa_foreign");
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranstok');
    }
}
