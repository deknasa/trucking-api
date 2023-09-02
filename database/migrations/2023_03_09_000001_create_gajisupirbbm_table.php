<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class CreateGajisupirbbmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gajisupirbbm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gajisupir_id')->nullable();   
            $table->string('gajisupir_nobukti', 50)->nullable();            
            $table->string('penerimaantrucking_nobukti', 50)->nullable();            
            $table->string('pengeluarantrucking_nobukti', 50)->nullable();            
            $table->unsignedBigInteger('supir_id')->nullable();   
            $table->double('nominal', 15,2)->nullable();    
            $table->longText('info')->nullable();        
            $table->string('modifiedby', 50)->nullable();            
            $table->timestamps();

            $table->foreign('supir_id', 'gajisupirbbm_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('gajisupir_id', 'gajisupirbbm_gajisupirheader_gajisupir_id_foreign')->references('id')->on('gajisupirheader')->onDelete('cascade');    

        });

        DB::statement("ALTER TABLE gajisupirbbm NOCHECK CONSTRAINT gajisupirbbm_supir_supir_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gajisupirbbm');
    }
}
