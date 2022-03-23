<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarifTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarif', function (Blueprint $table) {
            $table->id();
            $table->string('tujuan',200)->default('');
            $table->unsignedBigInteger('container_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->string('tujuanasal',300)->default('');
            $table->integer('sistemton')->length(11)->default('0');
            $table->unsignedBigInteger('kota_id')->default('0');
            $table->unsignedBigInteger('zona_id')->default('0');
            $table->double('nominalton',15,2)->default('0');
            $table->date('tglberlaku')->default('1900/1/1');
            $table->integer('statuspenyesuaianharga')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');
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
        Schema::dropIfExists('tarif');
    }
}
