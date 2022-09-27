<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaantruckingdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaantruckingdetail');

        Schema::create('penerimaantruckingdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaantruckingheader_id');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->string('pengeluarantruckingheader_nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();


            $table->foreign('penerimaantruckingheader_id', 'penerimaantruckingdetail_penerimaantruckingheader_penerimaantruckingheader_id_foreign')->references('id')->on('penerimaantruckingheader')->onDelete('cascade');    
            $table->foreign('supir_id', 'penerimaantruckingdetail__supir_supir_id_foreign')->references('id')->on('supir');    



        });

        DB::statement("ALTER TABLE penerimaantruckingdetail NOCHECK CONSTRAINT penerimaantruckingdetail__supir_supir_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaantruckingdetail');
    }
}
