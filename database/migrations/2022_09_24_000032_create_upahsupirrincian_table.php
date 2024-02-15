<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUpahsupirrincianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('upahsupirrincian');

        Schema::create('upahsupirrincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('statuscontainer_id')->nullable();
            $table->double('nominalsupir',15,2)->nullable();
            $table->double('nominalkenek',15,2)->nullable();
            $table->double('nominalkomisi',15,2)->nullable();
            $table->double('nominaltol',15,2)->nullable();
            $table->double('liter',15,2)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            
            $table->timestamps();

            $table->foreign('upahsupir_id', 'upahsupirrincian_upahsupir_upahsupir_id_foreign')->references('id')->on('upahsupir')->onDelete('cascade');
            $table->foreign('container_id', 'upahsupirrincian_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('statuscontainer_id', 'upahsupirrincian_statuscontainer_statuscontainer_id_foreign')->references('id')->on('statuscontainer');


        });

        DB::statement("ALTER TABLE upahsupirrincian NOCHECK CONSTRAINT upahsupirrincian_container_container_id_foreign");
        DB::statement("ALTER TABLE upahsupirrincian NOCHECK CONSTRAINT upahsupirrincian_statuscontainer_statuscontainer_id_foreign");
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
