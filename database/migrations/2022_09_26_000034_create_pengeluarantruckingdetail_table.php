<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluarantruckingdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengeluarantruckingdetail');

        Schema::create('pengeluarantruckingdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluarantruckingheader_id');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->string('penerimaantruckingheader_nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');        
            $table->longText('keterangan')->default('');    
            $table->string('modifiedby',50)->default('');                  
            $table->timestamps();

      
            $table->foreign('pengeluarantruckingheader_id', 'pengeluarantruckingdetail_pengeluarantruckingheader_pengeluarantruckingheader_id_foreign')->references('id')->on('pengeluarantruckingheader')->onDelete('cascade');    
            $table->foreign('supir_id', 'pengeluarantruckingdetail__supir_supir_id_foreign')->references('id')->on('supir');    

        });

        DB::statement("ALTER TABLE pengeluarantruckingdetail NOCHECK CONSTRAINT pengeluarantruckingdetail__supir_supir_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarantruckingdetail');
    }
}
