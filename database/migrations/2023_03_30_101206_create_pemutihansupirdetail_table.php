<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePemutihansupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pemutihansupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pemutihansupir_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('pengeluarantrucking_nobukti', 50)->nullable();   
            $table->integer('statusposting')->length(11)->nullable();
            $table->double('nominal', 15,2)->nullable();   
            $table->longText('info')->nullable();           
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('pemutihansupir_id', 'pemutihansupirdetail_pemutihansupirheader_pemutihansupir_id_foreign')->references('id')->on('pemutihansupirheader')->onDelete('cascade');    

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pemutihansupirdetail');
    }
}
