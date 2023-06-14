<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAkunpusatdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('akunpusatdetail', function (Blueprint $table) {
            $table->id();
            $table->string('coa', 50)->nullable();
            $table->integer('bulan')->length(11)->nullable();
            $table->integer('tahun')->length(11)->nullable();
            $table->double('nominal',15,2)->nullable();             
            $table->string('modifiedby',50)->nullable();                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('akunpusatdetail');
    }
}