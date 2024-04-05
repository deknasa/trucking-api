<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaanstokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaanstok');

        Schema::create('penerimaanstok', function (Blueprint $table) {
            $table->id();
            $table->longText('kodepenerimaan')->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->string('coa',50)->nullable();            
            $table->unsignedBigInteger('format')->nullable();            
            $table->integer('statushitungstok')->length(11)->nullable();         
            $table->integer('urutfifo')->length(11)->nullable();         
            $table->unsignedBigInteger('aco_id')->nullable();          
            $table->unsignedBigInteger('cabang_id')->nullable();          
            $table->integer('statusaktif')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();              
            $table->string('modifiedby',50)->nullable();              
            $table->timestamps();

            $table->foreign('coa', 'penerimaanstok_akunpusat_coa_foreign')->references('coa')->on('akunpusat');  
        });

        DB::statement("ALTER TABLE penerimaanstok NOCHECK CONSTRAINT penerimaanstok_akunpusat_coa_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaanstok');
    }
}
