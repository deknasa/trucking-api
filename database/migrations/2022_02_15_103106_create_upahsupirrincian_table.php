<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpahsupirrincianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upahsupirrincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upahsupir_id')->default('0');
            $table->unsignedBigInteger('container_id')->default('0');
            $table->unsignedBigInteger('statuscontainer_id')->default('0');
            $table->double('nominalsupir',15,2)->default('0');
            $table->double('nominalkenek',15,2)->default('0');
            $table->double('nominalkomisi',15,2)->default('0');
            $table->double('nominaltol',15,2)->default('0');
            $table->double('liter',15,2)->default('0');
            $table->string('modifiedby',50)->Default('');            
            $table->timestamps();

            $table->foreign('upahsupir_id')->references('id')->on('upahsupir')->onDelete('cascade');             
            $table->foreign('container_id')->references('id')->on('container');
            $table->foreign('statuscontainer_id')->references('id')->on('statuscontainer');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upahsupirrincian');
    }
}
