<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarifhargatertentuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarifhargatertentu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('tujuanbongkar', 500)->nullable();
            $table->string('lokasidooring', 500)->nullable();
            $table->unsignedBigInteger('lokasidooring_id')->nullable();
            $table->string('shipper', 500)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('cabang', 500)->nullable();
            $table->integer('statuscabang')->length(11)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->longText('info')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();   
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('tarif_id', 'tarif_tarifhargatertentu_tarif_id_foreign')->references('id')->on('tarif')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tarifhargatertentu');
    }
}
