<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataritasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dataritasi', function (Blueprint $table) {
            $table->id();
            $table->integer('statusritasi')->nullable();            
            $table->double('nominal',15,2)->nullable();
            $table->integer('statusaktif')->nullable();  
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();          
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
        Schema::dropIfExists('dataritasi');
    }
}
