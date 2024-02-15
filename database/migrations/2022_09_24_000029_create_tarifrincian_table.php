<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTarifrincianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tarifrincian');

        Schema::create('tarifrincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();                 
            $table->timestamps();

            $table->foreign('tarif_id', 'tarif_tarif_tarif_id_foreign')->references('id')->on('tarif')->onDelete('cascade');
            $table->foreign('container_id', 'tarif_container2_container_id_foreign')->references('id')->on('container');

        });

        DB::statement("ALTER TABLE tarifrincian NOCHECK CONSTRAINT tarif_container2_container_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tarifrincian');
    }
}
