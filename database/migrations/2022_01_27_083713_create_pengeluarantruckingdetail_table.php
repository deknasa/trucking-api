<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluarantruckingdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluarantruckingdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluarantruckingheader_id');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->string('penerimaantruckingheader_nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');                  
            $table->timestamps();

            $table->foreign('pengeluarantruckingheader_id')->references('id')->on('pengeluarantruckingheader')->onDelete('cascade');            
            $table->foreign('supir_id')->references('id')->on('supir');

        });
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
