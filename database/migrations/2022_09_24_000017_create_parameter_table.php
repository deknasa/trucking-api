<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParameterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('parameter');
        
        Schema::create('parameter', function (Blueprint $table) {
            $table->id();
            $table->string('grp', 255)->nullable();
            $table->string('subgrp', 255)->nullable();
            $table->string('kelompok', 255)->nullable();
            $table->string('text', 255)->nullable();
            $table->longText('memo')->nullable();
            $table->integer('type')->length(11)->nullable();
            $table->string('default', 255)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            
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
        Schema::dropIfExists('parameter');
    }
}
