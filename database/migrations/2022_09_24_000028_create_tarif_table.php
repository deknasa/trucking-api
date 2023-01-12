<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTarifTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('tarif');

        Schema::create('tarif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default('0');
            $table->unsignedBigInteger('upahsupir_id')->default('0');
            $table->string('tujuan',200)->default('');
            $table->unsignedBigInteger('container_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->integer('statussistemton')->length(11)->default('0');
            $table->unsignedBigInteger('kota_id')->default('0');
            $table->unsignedBigInteger('zona_id')->default('0');
            $table->double('nominalton',15,2)->default('0');
            $table->date('tglmulaiberlaku')->default('1900/1/1');
            $table->integer('statuspenyesuaianharga')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('container_id', 'tarif_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('kota_id', 'tarif_kota_kota_id_foreign')->references('id')->on('kota');
            $table->foreign('zona_id', 'tarif_zona_zona_id_foreign')->references('id')->on('zona');
        });

        DB::statement("ALTER TABLE tarif NOCHECK CONSTRAINT tarif_container_container_id_foreign");
        DB::statement("ALTER TABLE tarif NOCHECK CONSTRAINT tarif_kota_kota_id_foreign");
        DB::statement("ALTER TABLE tarif NOCHECK CONSTRAINT tarif_zona_zona_id_foreign");
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
