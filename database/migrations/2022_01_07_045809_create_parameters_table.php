<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tparameter', function (Blueprint $table) {
            $table->id();
            $table->string('modifiedby', 30)->nullable();
            $table->string('grp', 255);
            $table->string('subgrp', 255);
            $table->string('text', 255);
            $table->mediumText('memo');
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
        Schema::dropIfExists('parameters');
    }
}
