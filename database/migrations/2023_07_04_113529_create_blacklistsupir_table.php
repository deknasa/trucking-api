<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlacklistsupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blacklistsupir', function (Blueprint $table) {
            $table->id();
            $table->string('namasupir', 500)->nullable();
            $table->string('noktp', 500)->nullable();
            $table->string('nosim', 500)->nullable();
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
        Schema::dropIfExists('blacklistsupir');
    }
}
