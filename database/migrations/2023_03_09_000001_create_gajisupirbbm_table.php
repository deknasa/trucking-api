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
            $table->unsignedBigInteger('gajisupir_id')->default(0);   
            $table->string('gajisupir_nobukti', 50)->default('');            
            $table->string('penerimaantrucking_nobukti', 50)->default('');            
            $table->string('pengeluarantrucking_nobukti', 50)->default('');            
            $table->unsignedBigInteger('supir_id')->default(0);   
            $table->double('nominal', 15,2)->default(0);            
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();

            $table->foreign('supir_id', 'gajisupirbbm_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('gajisupir_id', 'gajisupirbbm_gajisupirheader_gajisupir_id_foreign')->references('id')->on('gajisupirheader');    

        });

        DB::statement("ALTER TABLE gajisupirbbm NOCHECK CONSTRAINT gajisupirbbm_supir_supir_id_foreign");
        DB::statement("ALTER TABLE gajisupirbbm NOCHECK CONSTRAINT gajisupirbbm_gajisupirheader_gajisupir_id_foreign");
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
