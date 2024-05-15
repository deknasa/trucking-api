<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGandenganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gandengan', function (Blueprint $table) {
            $table->id();
            $table->string('kodegandengan', 300)->nullable();
            $table->string('keterangan', 300)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->integer('jumlahroda')->length(11)->nullable();
            $table->integer('jumlahbanserap')->length(11)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statusjeniskendaraan')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 30)->nullable();            
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
        Schema::dropIfExists('gandengan');
    }
}
