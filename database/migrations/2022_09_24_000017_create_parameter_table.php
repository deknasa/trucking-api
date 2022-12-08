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
            $table->string('grp', 255)->default('');
            $table->string('subgrp', 255)->default('');
            $table->string('kelompok', 255)->default('');
            $table->string('text', 255)->default('');
            $table->longText('memo')->default('');
            $table->integer('type')->length(11)->default(0);
            $table->string('singkatan', 100)->default('');
            $table->string('warna', 255)->default('');
            $table->string('modifiedby', 50)->default('');
            
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
