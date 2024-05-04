<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateGajisupiruangjalanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gajisupiruangjalan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gajisupir_id')->nullable();
            $table->string('gajisupir_nobukti', 50)->nullable();
            $table->string('absensisupir_nobukti', 50)->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->integer('statusjeniskendaraan')->Length(11)->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longText('info')->nullable();
            $table->timestamps();

            $table->foreign('supir_id', 'gajisupiruangjalan_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('gajisupir_id', 'gajisupiruangjalan_gajisupirheader_gajisupir_id_foreign')->references('id')->on('gajisupirheader');
        });

        DB::statement("ALTER TABLE gajisupiruangjalan NOCHECK CONSTRAINT gajisupiruangjalan_supir_supir_id_foreign");
        DB::statement("ALTER TABLE gajisupiruangjalan NOCHECK CONSTRAINT gajisupiruangjalan_gajisupirheader_gajisupir_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gajisupiruangjalan');
    }
}
