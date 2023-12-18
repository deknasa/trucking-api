<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtobonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otobon', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->double('nominal',15,2)->nullable();   
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
        Schema::dropIfExists('otobon');
    }
}
